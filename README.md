# megadown

Bash script for download files from MEGA.NZ and MegaCrypter

## Features:

 * /#!, /#N!, mega://enc?, mega://enc2 and ANY megacrypter clon link supported
 * Resume previous downloads
 * Download files from list
 * Speed limit
 * MC password protected links supported

## Required dependencies:

 * Bash >= 3
 * OpenSSL with support for AES 128 CTR and AES 128/256 CBC
 * wget/curl (curl is preferred if it's present)
 * pv (monitor the progress of data)
 * jq (JSON parser)

## Optional dependencies:
 * python >= 2.7.8 (PBKDF2 -> MegaCrypter password protected links)

## Usage:

```bash
Single url mode:            megadown ['URL'] [OPTION]...

	Options explanation:
	-o,	--output FILE_NAME    Store file with this name (.
	-s,	--speed SPEED         Download speed limit (500b, 500k, 2m).
	-p,	--password PASSWORD   Password for MegaCrypter links.
	-q,     --quiet               Quiet mode.
        -m,     --metadata            Prints file metadata and exits. (File name is base64 encoded).

Multi url mode:             megadown [-l URL_LIST_FILE] [OPTION]...

	Options explanation:
	-s,     --speed SPEED         Download speed limit (integer values: 500B, 500K, 2M).
        -p,     --password PASSWORD   Password for MegaCrypter links (same for every link in a list).
        -q,     --quiet               Quiet mode.
        -m,     --metadata            Prints file metadata and exits. (File name is base64 encoded).
        File line format:          URL [optional_file_name]

```
