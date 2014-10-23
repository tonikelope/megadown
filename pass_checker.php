<?php

	function check_password($password, $mc_pass) {
		
		list($iter_log2, $key_check, $salt) = explode('#', $mc_pass);
		
		$hmac = passHMAC('sha256', $password, base64_decode($salt), pow(2,$iter_log2));
		
		return (hash('sha256',$hmac, true) == base64_decode($key_check))?bin2hex($hmac):'bad-password';
	}
	
	function passHMAC($algo, $pass, $salt, $iterations, $raw_output=true) {
		
        for($i=1, $xor=($last=hash_hmac($algo, $salt, $pass, true)); $i<$iterations; $i++) {

            $xor^=($last=hash_hmac($algo, $last, $pass, true));
        }

        return $raw_output?$xor:bin2hex($xor);
    }
    
    echo check_password($argv[1], $argv[2]);
    
?>
