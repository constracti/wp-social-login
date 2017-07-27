#!/bin/bash

wget -N https://curl.haxx.se/ca/cacert.pem
zip kgr-social-login.zip -u -r composer/ images/ *.css *.js *.php cacert.pem
