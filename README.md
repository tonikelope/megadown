# megadown

Bash script for download files from mega.nz and megacrypter

## Features

 * /#!, /#N!, mega://enc?, mega://enc2 and ANY megacrypter clon link supported
 * Resume previous downloads
 * MC password protected links supported
 * Download files from list
 * Speed limit

## Dependencies:

 * OpenSSL (with support for AES 128 CTR and AES 128 CBC)
 * php-cli (for JSON decoding and fast MC password check)
 * pv (monitor the progress of data)

## Usage

```bash
./megadown '[link]' [-o|--output new_file_name] [-s|--speed speed_limit_bytes_second] [-p|--password mc_url_pass]
./megadown [-l|--list file_list] [-s|--speed speed_limit_bytes_second] [-p|--password mc_url_pass]
```

## Parameters

 * `-l | --list`                  get links from text file. To set new names to listed files, add the new name after a space for each line like `https://mega-link/ new-name.zip`
 * `-o | --output`                store file with this name
 * `-s | --speed`                 download speed limit (500b, 500k, 2m)
 * `-p | --password`              password to protected files
