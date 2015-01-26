megadown
========

Bash script for download files from mega.co.nz and megacrypter

Features:

*Resume previous downloads

*MC password protected links supported

Dependencies:

* OpenSSL (with support for AES 128 CTR and AES 128 CBC)
* PHP-cli (for MC password protected links)
* pv

Example (remember chmod u+x):

```
$ /path/to/megadown.sh 'https://mega.co.nz/#!xxxxxxxxxx!xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx'

$ /path/to/megadown.sh 'http://megacrypter_domain/!xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx!xxxxxxxx'
```

If you have a link list you can download it with:

```
$ < /path/to/link/list.txt xargs -I % /path/to/megadown.sh %
```
