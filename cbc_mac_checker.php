<?php

	function urlBase64Decode($data) {
        	
        	return base64_decode(str_replace(['-', '_', ','], ['+', '/', ''], str_pad($data, strlen($data) + (4 - strlen($data) % 4) % 4, '=')));
    }

	function bin2i32a($bin) {
        	
        	return array_values(unpack('N*', str_pad($bin, 4 * ceil(strlen($bin) / 4), chr(0))));
    }
    
    function i32a2Bin(array $i32a) {
        return call_user_func_array('pack', array_merge(['N*'], $i32a));
    }
	
	function calculateChunkOffset($chunk_id) {        
        $offsets = array(0, 128, 384, 768, 1280, 1920, 2688);
        
        return ($chunk_id<=7?$offsets[$chunk_id-1]:(3584 + ($chunk_id-8)*1024))*1024;
    }
	
	function calculateChunkSize($chunk_id, $file_size) {
		
        $chunk_size = ($chunk_id>=1 && $chunk_id<=7)?$chunk_id*128*1024:1024*1024;
        
        $offset = calculateChunkOffset($chunk_id);
        
        if($offset + $chunk_size > $file_size) {
            $chunk_size = $file_size - $offset;
        }
        
        return $chunk_size;
    }
    
	function aesCbcEncrypt($data, $key, $iv = null, $pkcs7pad = false) {
        return mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $pkcs7pad ? self::pkcs7Pad($data, mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC)) : $data, MCRYPT_MODE_CBC, is_null($iv) ? pack('x' . mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC)) : $iv);
    }
    
    function getFileKeyi32a($key) 
    {
        for($i=0; $i<4; $i++) {
			
			$k[$i] = $key[$i]^$key[$i+4];
		}
		
        return $k;
    }
    
    function getIVi32a($key) {
	
		return array($key[4], $key[5]);
	}
	
	function getMetaMac($key) {
		
		return array($key[6], $key[7]);
	}
    
	function cbc_mac_check($file_key, $file_path, $file_size) {
		
		$file_key_i32a = bin2i32a(urlBase64Decode($file_key));
		
		$key = getFileKeyi32a($file_key_i32a);
		
		$iv = getIVi32a($file_key_i32a);
	
		$meta_mac = getMetaMac($file_key_i32a);

		$file_mac = array(0,0,0,0);

		$cbc_iv = array(0,0,0,0);

		$chunk_id = 1;

		$tot = 0;

		$fp = fopen($file_path, 'r');
		
		$cbc_mac_key = i32a2Bin($key);
		
		for($tot=0, $chunk=null; $tot<$file_size; $tot+=strlen($chunk))
		{
			$chunk_mac = array($iv[0], $iv[1], $iv[0], $iv[1]);
			
			$chunk = fread($fp, calculateChunkSize($chunk_id++, $file_size));
			
			echo "(".number_format(((float)$tot/(float)$file_size)*100, 1)." %) -> ";
			
			for($offset=0, $tot_block=0; $tot_block<strlen($chunk); $tot_block+=16, $offset++)
			{
				$block = array_pad(bin2i32a(substr($chunk, $offset*16, 16)), 4, chr(0));
			
				for($i=0; $i<count($chunk_mac); $i++)
				{
					$chunk_mac[$i]^=$block[$i];
				}
				
				$chunk_mac = bin2i32a(aesCbcEncrypt(i32a2Bin($chunk_mac), $cbc_mac_key, i32a2Bin($cbc_iv)));
			}
			
			for($i=0; $i<count($file_mac); $i++)
			{
				$file_mac[$i]^=$chunk_mac[$i];
			}

			$file_mac = bin2i32a(aesCbcEncrypt(i32a2Bin($file_mac), $cbc_mac_key, i32a2Bin($cbc_iv)));
		}
		
		fclose($fp);
		
		$cbc = array($file_mac[0]^$file_mac[1], $file_mac[2]^$file_mac[3]);
		
		return ($cbc[0] == $meta_mac[0] && $cbc[1]==$meta_mac[1]);
		
	}
	
	echo cbc_mac_check($argv[1], $argv[2], $argv[3])?"\nFILE IS OK :) !\n":"File is damaged :( !\n";
	
?>
