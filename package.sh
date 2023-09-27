#!/bin/bash

rm opencart-3.0.3-8-linux-amd64-debian-11.tar.gz
rm opencart-3.0.3-8-linux-amd64-debian-11.tar.gz.sha256
tar -czvf opencart-3.0.3-8-linux-amd64-debian-11.tar.gz opencart-3.0.3-8-linux-amd64-debian-11
sum="$(shasum -a 256 opencart-3.0.3-8-linux-amd64-debian-11.tar.gz)"
echo "$sum" > opencart-3.0.3-8-linux-amd64-debian-11.tar.gz.sha256

git add .
git commit -m "update opencart-3.0.3-8-linux-amd64-debian-11.tar.gz"
git push