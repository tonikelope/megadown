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

	$hmac = passwordHMAC('sha256', $password, base64_decode($salt), pow(2, $iter_log2));

	if (hash_hmac('sha256', $hmac, base64_decode($iv), true) !== base64_decode($key_check)) {
		return 'bad-password';
	}

	return bin2hex($hmac).'#'.bin2hex(base64_decode($iv));
}

function passwordHMAC($algo, $pass, $salt, $iterations)
{
	for ($i = 1, $xor = ($last = hash_hmac($algo, $salt, $pass, true)); $i < $iterations; $i++) {
		$xor ^= ($last = hash_hmac($algo, $last, $pass, true));
	}

	return $xor;
}

echo call_user_func_array($argv[1], array_slice($argv, 2));
