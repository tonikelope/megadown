<?php
function jsonParam($json, $var)
{
	$val = json_decode($json);
	$val = is_array($val) ? $val[0] : $val;

	if (is_bool($val->$var)) {
		return $val->$var ? '1' : '0';
	} elseif (isset($val->$var)) {
		return $val->$var;
	} else {
		return '';
	}
}

function passwordCheck($password, $mc_pass)
{
	list($iter_log2, $key_check, $salt) = explode('#', $mc_pass);

	$hmac = passwordHMAC('sha256', $password, base64_decode($salt), pow(2, $iter_log2));

	if (hash('sha256', $hmac, true) !== base64_decode($key_check)) {
		return 'bad-password';
	}

	return bin2hex($hmac);
}

function passwordHMAC($algo, $pass, $salt, $iterations)
{
	for ($i = 1, $xor = ($last = hash_hmac($algo, $salt, $pass, true)); $i < $iterations; $i++) {
		$xor ^= ($last = hash_hmac($algo, $last, $pass, true));
	}

	return $xor;
}

if (!function_exists($argv[1])) {
	die(sprintf("Function %s not exists", $argv[1]));
}

echo $argv[1]($argv[2], $argv[3]);
