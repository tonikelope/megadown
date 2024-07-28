# megadown

Bash script for download files and get metadata from mega.nz and megacrypter links.

## Features:
 * Retrieve metadata (filename and filesize) from any mega/megacrypter link without download.
 * /#!, /#N!, mega://enc?, mega://enc2 and ANY megacrypter clon link supported.
 * Resume previous downloads.
 * Download files from list.
 * Speed limit.
 * MC password protected links supported.

## Required dependencies:

 * Bash >= 3
 * OpenSSL (with support for AES 128 CTR and AES 128/256 CBC).
 * wget/curl (curl is preferred if it's present).
 * pv (monitor the progress of data).
 * jq (JSON parser).

## Optional dependencies:
 * python >= 2.7.8 (PBKDF2 -> MegaCrypter password protected links).

## Usage:

```bash
megadown 1.9.47 - https://github.com/tonikelope/megadown

cli downloader for mega.nz and megacrypter

Single url mode:           megadown [OPTION]... 'URL'

        Options:
        -o,     --output FILE_NAME    Store file with this name.
        -x,     --proxy PROXY         Proxy (curl)
        -s,     --speed SPEED         Download speed limit (integer values: 500B, K, 2M).
        -p,     --password PASSWORD   Password for MegaCrypter links.
        -q,     --quiet               Quiet mode.
        -m,     --metadata            Prints file metadata in JSON format and exits.


Multi url mode:          megadown [OPTION]... -l|--list FILE

        Options:
        -x,     --proxy PROXY         Proxy (curl)
        -s,     --speed SPEED         Download speed limit (integer values: 500B, 500K, 2M).
        -p,     --password PASSWORD   Password for MegaCrypter links (same for every link in a list).
        -q,     --quiet               Quiet mode.
        -m,     --metadata            Prints file metadata in JSON format and exits.
        File line format:          URL [optional_file_name]

```
