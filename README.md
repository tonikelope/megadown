megadown
========

Bash script for download files from mega.co.nz and megacrypter.com

Dependencies:

OpenSSL (with support for AES 128 CTR and AES 128 CBC)

perl

pv


If you have a link list you can download with:

$ < list.txt xargs -I % ./megadown.sh %