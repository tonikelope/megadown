<?php
function jsonParam($json, $var)
{
	$val = json_decode($json);
	
	$val = is_array($val) ? $val[0] : $val;

	if (isset($val->$var) && is_bool($val->$var)) {
		return $val->$var ? '1' : '0';
	} elseif (isset($val->$var)) {
		return $val->$var;
	} else {
		return '';
	}
}

function passwordCheck($password, $mc_pass)
{
	list($iter_log2, $key_check, $salt, $iv) = explode('#', $mc_pass);

	$hmac = passwordHMAC('sha256', base64_decode($salt), $password, pow(2, $iter_log2));

	if (hash_hmac('sha256', $hmac, base64_decode($iv), true) !== base64_decode($key_check)) {
		return 'bad-password';
	}

	return bin2hex($hmac).'#'.bin2hex(base64_decode($iv));
}

function passwordHMAC($algo, $salt, $pass, $iterations)
{
	# php_hmac (algorithm, data, key, bin_output)
	for ($i = 1, $xor = ($last = hash_hmac($algo, $salt, $pass, true)); $i < $iterations; $i++) {
		$xor ^= ($last = hash_hmac($algo, $last, $pass, true));
	}

	return $xor;
}

function aesCbcDecrypt($data, $key, $iv = null, $pkcs7pad = false) {
        $dec = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $data, MCRYPT_MODE_CBC, is_null($iv) ? pack('x' . mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC)) : $iv);

        return $pkcs7pad ? pkcs7UnPad($dec) : $dec;
    }
    
function pkcs7UnPad($data) {
        $pad = ord($data[strlen($data) - 1]);

        return ($pad > strlen($data) || strspn($data, chr($pad), strlen($data) - $pad) != $pad) ? false : substr($data, 0, -1 * $pad);
    }

function urlBase64Decode($data) {
        return base64_decode(str_replace(['-', '_', ','], ['+', '/', ''], str_pad($data, strlen($data) + (4 - strlen($data) % 4) % 4, '=')));
    }
    
function decryptMegaDownloaderLinks($data) {
        
        return preg_replace_callback('/mega\:\/\/(?P<folder>f)?(?P<enc>enc\d*?)\?(?P<linkdata>[\da-z_,-]*?)(?=https?\:|mega\:|[^\da-z_,-]|$)/i', 
                
            function($match) {
				
                $key = ['enc' => '6B316F36416C2D316B7A3F217A30357958585858585858585858585858585858', 
						'enc2' => 'ED1F4C200B35139806B260563B3D3876F011B4750F3A1A4A5EFD0BBE67554B44'];

                $iv = '79F10A01844A0B27FF5B2D4E0ED3163E';

                return 'https://mega.nz/#' . strtoupper($match['folder']) . aesCbcDecrypt(urlBase64Decode($match['linkdata']), hex2bin($key[$match['enc']]), hex2bin($iv), true); }, 
                
            $data);
    }
    
echo call_user_func_array($argv[1], array_slice($argv, 2));
