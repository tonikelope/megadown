#!/usr/bin/env python

import sys
import json
import hmac
import hashlib
import base64
import binascii

def json_param(json_data, json_var, index=0, undef_msg='', bool_msg={'true':1, 'false':0}):
	
	j = json.loads(json_data)

	if isinstance(j, list):
		j=j[index]

	if json_var not in j:
		j[json_var] = undef_msg
	elif isinstance(j[json_var], bool):
		j[json_var] = bool_msg['true'] if j[json_var] == True else bool_msg['false']
		
	return j[json_var]


def password_check(password, mc_pass_resp, bad_pass_msg=0):

	mc_pass_items=mc_pass_resp.split('#')

	mc_pass_hash=password_hmac(base64.b64decode(mc_pass_items[2]), password.encode(), 2**int(mc_pass_items[0]))

	if not hmac.compare_digest(hmac.new(base64.b64decode(mc_pass_items[3]), mc_pass_hash, hashlib.sha256).digest(), base64.b64decode(mc_pass_items[1])):
		return bad_pass_msg

	return binascii.hexlify(mc_pass_hash).decode()+'#'+binascii.hexlify(base64.b64decode(mc_pass_items[3])).decode()


def password_hmac(data, secret, iterations):

	i=1
	xor = bytearray(hmac.new(secret, data, hashlib.sha256).digest())
	last = xor
	
	while i<iterations:

		last = bytearray(hmac.new(secret, last, hashlib.sha256).digest())
		xor = [a ^ b for a, b in zip(xor, last)]
		i+=1

	return xor if i == 1 else bytearray(xor)


def call_user_func_array(callback, param_arr):

	if callback in globals():
		return globals()[callback](*param_arr)
	else:
		return 'ERROR: '+callback+' is not defined!'


if __name__ == '__main__':
	print(call_user_func_array(sys.argv[1], sys.argv[2:]))