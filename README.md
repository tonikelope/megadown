megadown
========

Bash script for download files from mega.co.nz and megacrypter.com

Features:

*Resume previous downloads

Dependencies:

* OpenSSL (with support for AES 128 CTR and AES 128 CBC)
* perl
* pv

Example (remember chmod u+x):

```
$ /path/to/megadown.sh 'https://mega.co.nz/#!xxxxxxxxxx!xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx'

$ /path/to/megadown.sh 'http://megacrypter.com/!xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx!xxxxxxxx'
```

If you have a link list you can download it with:

```
$ < /path/to/link/list.txt xargs -I % /path/to/megadown.sh %
```
