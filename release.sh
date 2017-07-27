#!/bin/bash

wget -N https://curl.haxx.se/ca/cacert.pem
zip kgr-social-login.zip -u -r images/ *.css *.js *.php cacert.pem
