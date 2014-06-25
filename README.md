megadown
========

Bash script for download files from mega.co.nz and megacrypter.com

Dependencies:

* OpenSSL (with support for AES 128 CTR and AES 128 CBC)
* perl
* pv

Example:

```
$ /path/to/megadown.sh 'http://megacrypter.com/!EVfq17sWkcVwMGtPM7y9sfWiv2ePS6KKzpoS4n7UdT8JMEr9T4j42_bNrrbEmuW8kRtqyUOPkWn-jisD-fsqU5q03Sp47xdcku3ZRsAVZR_wdb0M!8204e491'
```

If you have a link list you can download with:

```
$ < /path/to/link/list.txt xargs -I % /path/to/megadown.sh %
```
