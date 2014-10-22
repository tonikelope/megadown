<?php

	function check_password($password, $mc_pass) {
		
		list($iter_log2, $key_check, $salt) = explode('#', $mc_pass);
		
		$hmac = passHMAC('sha256', $password, base64_decode($salt), pow(2,$iter_log2));
		
		return (hash('sha256',$hmac, true) == base64_decode($key_check))?bin2hex($hmac):'bad-password';
	}
	
	function passHMAC($algo, $pass, $salt, $iterations, $raw_output=true) {
		
        for($i=0, $u=hash_hmac($algo, $salt, $pass, $raw_output); $i<$iterations-1; $i++) {

            $u^=hash_hmac($algo, $u, $pass, $raw_output);
        }

        return $u;
    }
    
    echo check_password($argv[1], $argv[2]);
    
?>
