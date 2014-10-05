#!/usr/bin/env bash
sudo su
export DEBIAN_FRONTEND=noninteractive

apt-get update -y

apt-get install -y -q mysql-server mysql-client git php5-cli php5-mysql php5-curl php5-intl php5-json curl
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

mysql -uroot -e "CREATE DATABASE IF NOT EXISTS uwc DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;"
mysql -uroot uwc < /vagrant/uwc.sql

# for cloning from github
mkdir ~/www
cd ~/www
git clone https://github.com/MetalGuardian/uwcVI.git .
composer install
cp /vagrant/config.php.sample ~/www/config.php
# cloning done


cd ~/www/public
nohup php -S 0.0.0.0:8000 &
