# megadown

Bash script for download files from mega.nz and megacrypter

## Features:

 * /#!, /#N!, mega://enc?, mega://enc2 and ANY megacrypter clon link supported
 * Resume previous downloads
 * MC password protected links supported
 * Download files from list
 * Speed limit

## Dependencies:

 * Bash >= 3
 * OpenSSL with support for AES 128 CTR and AES 128/256 CBC (crypto stuff)
 * python >= 2.6 (JSON parsing and MC password protected links)
 * wget/curl (downloading (curl is preferred if it's present))
 * pv (monitor the progress of data)

## Usage:

```bash
Single url mode:            megadown ['URL'] [OPTION]...

	Options explanation:
	-o,	--output FILE_NAME    Store file with this name (.
	-s,	--speed SPEED         Download speed limit (500b, 500k, 2m).
	-p,	--password PASSWORD   Password for MegaCrypter links.

Multi url mode:             megadown [-l URL_LIST_FILE] [OPTION]...

	File line format:         URL [optional_file_name]

	Options explanation:
	-s,	--speed SPEED         Download speed limit (500b, 500k, 2m).
	-p,	--password PASSWORD   Password for MegaCrypter links (same for every link in a list).

```
