<?php

#Remember: hash_hmac($algorithm, $data, $secret, $bin_output)

class MegadownHelpers{

	public static function json_param($json, $var, $index=0, $undefined='', $bool=array('true' => 1, 'false' => 0))
	{
		$val = json_decode($json);
		
		$val = is_array($val) ? $val[$index] : $val;

		if (isset($val->$var) && is_bool($val->$var)) {

			return $val->$var ? $bool['true'] : $bool['false'];

		} elseif (isset($val->$var)) {

			return $val->$var;

		} else {

			return $undefined;
		}
	}


	public static function password_check($password, $mc_pass, $bad_pass=0)
	{
		list($iter_log2, $key_check, $salt, $iv) = explode('#', $mc_pass);

		$hmac = self::password_hmac('sha256', base64_decode($salt), $password, pow(2, $iter_log2));

		if (hash_hmac('sha256', $hmac, base64_decode($iv), true) !== base64_decode($key_check)) {

			return $bad_pass;
		}

		return bin2hex($hmac).'#'.bin2hex(base64_decode($iv));
	}


	private static function password_hmac($algo, $salt, $pass, $iterations)
	{
		for ($i = 1, $xor = ($last = hash_hmac($algo, $salt, $pass, true)); $i < $iterations; $i++) {

			$xor ^= ($last = hash_hmac($algo, $last, $pass, true));
		}

		return $xor;
	}
}
    
echo call_user_func_array("MegadownHelpers::{$argv[1]}", array_slice($argv, 2));
