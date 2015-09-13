#!/bin/bash

VERSION="1.7.4"

HERE=$(dirname "$0")
SCRIPT=$(readlink -f "$0")
PHP_HELPERS="php $HERE/helpers.php"

MEGA_API_URL="https://g.api.mega.co.nz"
OPENSSL_AES_CTR_128_DEC="openssl enc -d -aes-128-ctr"
OPENSSL_AES_CBC_128_DEC="openssl enc -a -A -d -aes-128-cbc"
OPENSSL_AES_CBC_256_DEC="openssl enc -a -A -d -aes-256-cbc"

function showHelp {
	echo -e "\nmegadown v$VERSION - https://github.com/tonikelope/megadown"
	echo -e "\nmega.co.nz and megacrypter download bash script"

	echo -e "\nUsage"
	echo -e "\t./megadown '[link]' [-k|--key mega_api_key] [-o|--output new_file_name] [-s|--speed speed_limit_bytes_second] [-p|--password mc_url_pass]"
	echo -e "\t./megadown [-l|--list file_list] [-k|--key mega_api_key] [-s|--speed speed_limit_bytes_second] [-p|--password mc_url_pass]"

	echo -e "\nParameters"
	echo -e "\t-l | --list      get links from text file. To set new names to list files, add the new name after a space for each line."
	echo -e "\t-o | --output    store file with this name"
	echo -e "\t-s | --speed     download speed limit (500b, 500k, 2m)"
	echo -e "\t-p | --password  password to protected files"
	echo -e "\t-k | --key       your mega API key"

	echo -e "\nDependencies:"
	echo -e "\t- OpenSSL  (with support for AES 128 CTR and AES 128 CBC)"
	echo -e "\t- php-cli  (for MC password protected links)"
	echo -e "\t- pv       (monitor the progress of data)"

	echo ''
}

function showError {
	echo -e "\n$1\n" 1>&2
	exit 1
}

if [ -z "$1" ]; then
	showHelp
	exit 1
fi

if [[ "$1" =~ ^http ]]; then
	link="$1"
fi

eval set -- "$(getopt -o "l:p:k:o:s:" -l "list:,password:,key:,output:,speed:" -- "$@")"

while true; do
	case "$1" in
		-l|--list)     list="$2";     shift 2;;
		-p|--password) password="$2"; shift 2;;
		-k|--key)      key="$2";      shift 2;;
		-o|--output)   output="$2";   shift 2;;
		-s|--speed)    speed="$2";    shift 2;;

		--) shift; break;;

		*)
			showHelp
			exit 1;;
	esac
done

if [ -z "$link" ]; then
	if [ -z "$list" ]; then
		showHelp
		showError "ERROR: MEGA link or --list parameter is required"
	elif [ ! -f "$list" ]; then
		showHelp
		showError "ERROR: MEGA list file ${list} not found"
	fi

	cat "$list" | while read line; do
		if [[ "$line" =~ ^http ]]; then
			link=$(echo ${line} | awk -F' ' '{print $1}' | xargs)
			output=$(echo ${line} | awk -F' ' '{$1="";print}' | xargs)

			$SCRIPT "$link" --output="$output" --password="$password" --key="$key" --speed="$speed"
		fi
	done

	exit 0
fi

# 1:b64_encoded_String
function b64_pad {
	b64=$(echo -ne "$1" | tr '\-_' '+/')
	pad=$(((4-${#1}%4)%4))

	for i in $(seq 1 $pad); do
		b64="${b64}="
	done

	echo -n "$b64"
}

# 1:hex_raw_key
function hrk2hk {
	hk[0]=$(( 0x${1:0:16} ^ 0x${1:32:16} ))
	hk[1]=$(( 0x${1:16:16} ^ 0x${1:48:16} ))

	printf "%016x" ${hk[*]}
} 

for i in pv openssl wget php; do
	if [ "`which $i`" == "" ]; then
		showHelp
		showError "ERROR: Command $i is required and not installed"
	fi
done

if [ $(echo -n "$link" | grep -E -o 'mega(\.co)?\.nz') ]; then
	#MEGA.CO.NZ LINK

	file_id=$(php -r "echo preg_replace('/^.*\/#!(.+)!.*$/', '\1', \$argv[1]);" "$link")
	file_key=$(php -r "echo preg_replace('/^.*\/#!.+!(.+)$/', '\1', \$argv[1]);" "$link")
	hex_raw_key=$(echo -n $(b64_pad $file_key) | base64 -d -i 2>/dev/null | od -An -t x1 | tr -d '\n ')
	mega_req_url="${MEGA_API_URL}/cs?id=$seqno&ak=${key}"
	mega_req_json="[{\"a\":\"g\", \"p\":\"$file_id\"}]"
	mega_res_json=$(wget -q --header='Content-Type: application/json' --post-data "$mega_req_json" -O - "$mega_req_url")

	if [ $(echo -n "$mega_res_json" | grep -E -o '\[\-[0-9]+\]') ]; then
		error_code=$(php -r "echo preg_replace('/^.*\[(.*?)\].*$/', '\$1',\$argv[1]);" "$mega_res_json")
		showError "ERROR: MEGA $error_code"
	fi

	file_size=$($PHP_HELPERS jsonParam "$mega_res_json" s)
	at=$($PHP_HELPERS jsonParam "$mega_res_json" at)
	hex_key=$(hrk2hk "$hex_raw_key")
	at_dec_json=$(echo -n $(b64_pad "$at") | $OPENSSL_AES_CBC_128_DEC -K $hex_key -iv "00000000000000000000000000000000" -nopad)

	if [ ! $(echo -n "$at_dec_json" | grep -E -o 'MEGA') ]; then
		showError "ERROR: MEGA bad link"
	fi

	if [ -z "$output" ]; then
		file_name=$($PHP_HELPERS jsonParam "$(echo -n "$at_dec_json" | grep -E -o '\{.+\}')" n)
	else
		file_name="$output"
	fi
	
	mega_req_json="[{\"a\":\"g\", \"g\":\"1\", \"p\":\"$file_id\"}]"
	mega_res_json=$(wget -q --header='Content-Type: application/json' --post-data "$mega_req_json" -O - "$mega_req_url")
	dl_temp_url=$($PHP_HELPERS jsonParam "$mega_res_json" g)
else
	#MEGACRYPTER LINK

	MC_API_URL=$(echo -n "$link" | grep -i -E -o 'https?://[^/]+')"/api"
	info_link=$(wget -q --header='Content-Type: application/json' --post-data "{\"m\":\"info\", \"link\":\"$link\"}" -O - "$MC_API_URL")

	if [ $(echo $info_link | grep '"error"') ]; then
		error_code=$($PHP_HELPERS jsonParam "$info_link" error)
		showError "ERROR: MEGACRYPTER $error_code"
	fi

	if [ -z "$output" ]; then
		file_name=$($PHP_HELPERS jsonParam "$info_link" name)
	else
		file_name="$output"
	fi
	
	path=$($PHP_HELPERS jsonParam "$info_link" path)

	if [ "$path" != "0" ] && [ -n "$path" ]; then
	
		if [ ! -d "$path" ]; then
		
			mkdir -p "$path"
		fi
		
		file_name="${path}${file_name}"
	fi

	mc_pass=$($PHP_HELPERS jsonParam "$info_link" pass)

	if [ "$mc_pass" != "0" ]; then
		if [ -z $pass ]; then
			if [ $($PHP_HELPERS passwordCheck "$pass" "$mc_pass") == "bad-password" ]; then
				pass=""
			fi
		fi

		if [ -z "$pass" ]; then
			read -e -p "Link is password protected. Enter password: " pass

			pass_hash=$($PHP_HELPERS passwordCheck "$pass" "$mc_pass")

			until [ "$pass_hash" != "bad-password" ]; do
				read -e -p "Wrong password! Try again: " pass
				pass_hash=$($PHP_HELPERS passwordCheck "$pass" "$mc_pass")
			done
		fi
		
		IFS='#' read -a array <<< "$pass_hash"
		
		pass_hash=${array[0]}
		
		iv=${array[1]}

		hex_raw_key=$(echo -n $(b64_pad $($PHP_HELPERS jsonParam "$info_link" key)) | $OPENSSL_AES_CBC_256_DEC -K $pass_hash -iv "$iv" | od -An -t x1 | tr -d '\n ')

		if [ -z "$output" ]; then
			file_name=$(echo -n $(b64_pad "$file_name") | $OPENSSL_AES_CBC_256_DEC -K $pass_hash -iv "$iv")
		fi
	else
		hex_raw_key=$(echo -n $(b64_pad $($PHP_HELPERS jsonParam "$info_link" key)) | base64 -d -i 2>/dev/null | od -An -t x1 | tr -d '\n ')
	fi

	file_size=$($PHP_HELPERS jsonParam "$info_link" size)
	hex_key=$(hrk2hk "$hex_raw_key")
	dl_link=$(wget -q --header='Content-Type: application/json' --post-data "{\"m\":\"dl\", \"link\":\"$link\"}" -O - "$MC_API_URL")

	if [ $(echo $dl_link | grep '"error"') ]; then
		error_code=$($PHP_HELPERS jsonParam "$dl_link" error)

		showError "ERROR: MEGACRYPTER $error_code"
	fi

	dl_temp_url=$($PHP_HELPERS jsonParam "$dl_link" url)
	
	if [ "$mc_pass" != "0" ]; then

		iv=$(echo -n $(b64_pad $($PHP_HELPERS jsonParam "$dl_link" pass)) | base64 -d -i 2>/dev/null | od -An -t x1 | tr -d '\n ')
		
		dl_temp_url=$(echo -n $(b64_pad "$dl_temp_url") | $OPENSSL_AES_CBC_256_DEC -K $pass_hash -iv "$iv")
	fi
fi

if [ -z "$speed" ]; then
	DL_COMMAND="wget -q -O - "
else
	DL_COMMAND="wget -q --limit-rate $speed -O - "
fi

if [ "$output" == "-" ]; then
	hex_iv="${hex_raw_key:32:16}0000000000000000"
	$DL_COMMAND "$dl_temp_url" | $OPENSSL_AES_CTR_128_DEC -K $hex_key -iv $hex_iv

	exit 0
fi

if [ "$file_size" -ge 1024 ]; then
	file_size_f="~"$(($file_size/(1024*1024)))" MB"
else
	file_size_f="${file_size} bytes"
fi

if [ -f "$file_name" ]; then
	actual_size=$(wc -c "${file_name}" | cut -f 1 -d ' ')

	if [ "$actual_size" == "$file_size" ]; then
		showError "WARNING: File ${file_name} exists. Download aborted."
	fi

	echo -e "\nFile ${file_name} exists but with different size (${file_size} vs ${actual_size} bytes). Downloading [${file_size_f}] ...\n"
else
	echo -e "\nDownloading ${file_name} [${file_size_f}] ...\n"
fi

dl_exit_code=1

until [ "$dl_exit_code" -eq 0 ]; do
	if [ -f "${file_name}.temp" ]; then
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

	if [ "$dl_exit_code" -ne 0 ]; then
		showError "Oooops, download failed! EXIT CODE -> ${dl_exit_code}"
	fi	
done

if [ ! -f "${file_name}.temp" ]; then
	showError "ERROR: FILE ${file_name} COULD NOT BE DOWNLOADED :(!"
fi

mv "${file_name}.temp" "${file_name}"

echo -e "\nFILE DOWNLOADED as ${file_name} :)!\n"
exit 0
