#!/bin/bash

VERSION="1.6.6"
MEGA_API_URL="https://g.api.mega.co.nz"
MEGA_API_KEY=""
OPENSSL_AES_CTR_128_DEC="openssl enc -d -aes-128-ctr"
OPENSSL_AES_CBC_128_DEC="openssl enc -a -A -d -aes-128-cbc"
OPENSSL_AES_CBC_256_DEC="openssl enc -a -A -d -aes-256-cbc"

# 1:json_string 2:index
function json_param {
	php -r '$val=json_decode($argv[1]); if(is_array($val)){$val=$val[0];} if(is_bool($val->$argv[2])){echo $val->$argv[2]?"1":"0";}else{echo $val->$argv[2];}' "$1" "$2"
}

# 1:b64_encoded_String
function b64_pad {
	
	b64=$(echo -ne "$1" | tr '\-_' '+/')
	
	pad=$(((4-${#1}%4)%4))
	
	for i in $(seq 1 $pad)
	do
		b64="${b64}="
	done
	
	echo -n "$b64"
}

# 1:hex_raw_key
function hrk2hk {
	key[0]=$(( 0x${1:0:16} ^ 0x${1:32:16} ))

	key[1]=$(( 0x${1:16:16} ^ 0x${1:48:16} ))

	printf "%016x" ${key[*]}
} 

echo -e "\nThis is MEGA-DOWN $VERSION - https://github.com/tonikelope/megadown"

if [ -z $1 ]
then
	echo -e "\n$0 <mega_url|mc_url> [speed_limit_bytes_second] [output_file] [mc_url_pass]\n\nNote: use '-' for output to STDOUT\n"
else
	
	if [ $(echo -n $1 | grep -E -o 'mega\.co\.nz') ]
	then
		
		#MEGA.CO.NZ LINK

		file_id=$(php -r "echo preg_replace('/^.*\/#!(.+)!.*$/', '\1', \$argv[1]);" "$1")
		
		file_key=$(php -r "echo preg_replace('/^.*\/#!.+!(.+)$/', '\1', \$argv[1]);" "$1")
	
		hex_raw_key=$(echo -n $(b64_pad $file_key) | base64 -d -i 2>/dev/null | od -An -t x1 | tr -d '\n ')
		
		mega_req_url="${MEGA_API_URL}/cs?id=$seqno&ak=$MEGA_API_KEY"
		
		mega_req_json="[{\"a\":\"g\", \"p\":\"$file_id\"}]"
		
		mega_res_json=$(wget -q --header='Content-Type: application/json' --post-data "$mega_req_json" -O - "$mega_req_url")
		
		if [ $(echo -n "$mega_res_json" | grep -E -o '\[\-[0-9]+\]') ]
		then
			error_code=$(php -r "echo preg_replace('/^.*\[(.*?)\].*$', \$argv[1]);" "$mega_res_json")

			echo -e "\nMEGA ERROR: $error_code\n" 1>&2
			exit
		else	
			file_size=$(json_param "$mega_res_json" s)
			
			at=$(json_param "$mega_res_json" at)

			hex_key=$(hrk2hk "$hex_raw_key")
			
			at_dec_json=$(echo -n $(b64_pad "$at") | $OPENSSL_AES_CBC_128_DEC -K $hex_key -iv "00000000000000000000000000000000" -nopad)
			
			if [ $(echo -n "$at_dec_json" | grep -E -o 'MEGA') ]
			then
				
				file_name=$(json_param "$(echo -n "$at_dec_json" | grep -E -o '\{.+\}')" n)

				mega_req_json="[{\"a\":\"g\", \"g\":\"1\", \"p\":\"$file_id\"}]"
				
				mega_res_json=$(wget -q --header='Content-Type: application/json' --post-data "$mega_req_json" -O - "$mega_req_url")
				
				dl_temp_url=$(json_param "$mega_res_json" g)
			else
				echo -e "\nMEGA ERROR: bad link\n" 1>&2
				exit
			fi
		fi
	else
		
		#MEGACRYPTER LINK
		
		MC_API_URL=$(echo -n $1 | grep -i -E -o 'https?://[^/]+')"/api"
		
		info_link=$(wget -q --header='Content-Type: application/json' --post-data "{\"m\":\"info\", \"link\":\"$1\"}" -O - "$MC_API_URL")

		if [ $(echo $info_link | grep '"error"') ]
		then
			error_code=$(json_param "$info_link" error)
			echo -e "\nMEGACRYPTER ERROR: $error_code\n" 1>&2
			exit
		else
			if [ -z $3 ]
			then
				file_name=$(json_param "$info_link" name)
			else
				file_name="$3"
			fi		
			
			mc_pass=$(json_param "$info_link" pass)
			
			if [ "$mc_pass" != "0" ]
			then
				
				pass=""
				
				if [ $4 ]
				then
					pass="$4"
								
					if [ $(php $(dirname "$0")/pass_checker.php "$pass" "$mc_pass") == "bad-password" ]
					then
						pass=""
					fi
				fi
				
				if [ -z $pass ]
				then		
					read -e -p "Link is password protected. Enter password: " pass
					
					pass_hash=$(php $(dirname "$0")/pass_checker.php "$pass" "$mc_pass")
							
					until [ "$pass_hash" != "bad-password" ]; do
						read -e -p "Wrong password! Try again: " pass
						pass_hash=$(php $(dirname "$0")/pass_checker.php "$pass" "$mc_pass")
					done		
				fi

				hex_raw_key=$(echo -n $(b64_pad $(json_param "$info_link" key)) | $OPENSSL_AES_CBC_256_DEC -K $pass_hash -iv "00000000000000000000000000000000" | od -An -t x1 | tr -d '\n ')
				
				if [ -z $3 ]
				then
					file_name=$(echo -n $(b64_pad "$file_name") | $OPENSSL_AES_CBC_256_DEC -K $pass_hash -iv "00000000000000000000000000000000")
				fi
			else
				hex_raw_key=$(echo -n $(b64_pad $(json_param "$info_link" key)) | base64 -d -i 2>/dev/null | od -An -t x1 | tr -d '\n ')	
			fi

			file_size=$(json_param "$info_link" size)

			hex_key=$(hrk2hk "$hex_raw_key")

			dl_link=$(wget -q --header='Content-Type: application/json' --post-data "{\"m\":\"dl\", \"link\":\"$1\"}" -O - "$MC_API_URL")

			if [ $(echo $dl_link | grep '"error"') ]
			then
				error_code=$(json_param "$dl_link" error)
				echo -e "\nMEGACRYPTER ERROR: $error_code\n" 1>&2
				exit		
			else			
				dl_temp_url=$(json_param "$dl_link" url)
			fi
		fi
	fi
	
	if [ -z $2 ]
		then
			DL_COMMAND="wget -q -O - "
		else
			DL_COMMAND="wget -q --limit-rate $2 -O - "
		fi

	if [ "$3" != "-" ]
	then
	
		if [ $file_size -ge 1024 ]
		then
			file_size_f="~"$(($file_size/(1024*1024)))" MB"
		else
			file_size_f="${file_size} bytes"
		fi

		echo -e "\nDownloading ${file_name} [${file_size_f}] ...\n"

		dl_exit_code=1

		until [ $dl_exit_code -eq 0 ]; do

			if [ -f "${file_name}.temp" ]
			then
				echo -e "(Resuming previous download ...)\n"

				temp_size=$(stat -c %s "${file_name}.temp")

				offset=$(($temp_size-$(($temp_size%16))))

				iv_forward=$(printf "%016x" $(($offset/16)))

				hex_iv="${hex_raw_key:32:16}$iv_forward"

				truncate -s $offset "${file_name}.temp"

				$DL_COMMAND "$dl_temp_url/$offset" | pv -s $(($file_size-$offset)) | $OPENSSL_AES_CTR_128_DEC -K $hex_key -iv $hex_iv >> "${file_name}.temp"
			else
				hex_iv="${hex_raw_key:32:16}0000000000000000"				
				$DL_COMMAND "$dl_temp_url" | pv -s $file_size | $OPENSSL_AES_CTR_128_DEC -K $hex_key -iv $hex_iv > "${file_name}.temp"
			fi

			dl_exit_code=$?

			if [ $dl_exit_code -ne 0 ]
			then
				echo -e "\nOooops, download failed! EXIT CODE -> ${dl_exit_code}\n"
			fi	
		
		done

		mv "${file_name}.temp" "${file_name}"

		echo -e "\nFILE DOWNLOADED :)!\n"
			
	else
		hex_iv="${hex_raw_key:32:16}0000000000000000"
		$DL_COMMAND "$dl_temp_url" | $OPENSSL_AES_CTR_128_DEC -K $hex_key -iv $hex_iv
	fi
fi
