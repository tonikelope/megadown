# megadown

Bash script for download files from mega.co.nz and megacrypter

## Features

 * Resume previous downloads
 * MC password protected links supported
 * Download files from list

## Dependencies:

 * OpenSSL (with support for AES 128 CTR and AES 128 CBC)"
 * php-cli (for MC password protected links)"
 * pv (monitor the progress of data)"

## Usage

```bash
./megadown '[link]' [-k|--key mega_api_key] [-o|--output new_file_name] [-s|--speed speed_limit_bytes_second] [-p|--password mc_url_pass]
./megadown [-l|--list file_list] [-k|--key mega_api_key] [-s|--speed speed_limit_bytes_second] [-p|--password mc_url_pass]
```

## Parameters

 * `-l | --list`      get links from text file. To set new names to listed files, add the new name after a space for each line like `https://mega-link/ new-name.zip`
 * `-o | --output`    store file with this name
 * `-s | --speed`     download speed limit (500b, 500k, 2m)
 * `-p | --password`  password to protected files
 * `-k | --key`       your mega API key
