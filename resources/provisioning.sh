#!/bin/sh

if [ ! -f ~/.machine-bootstraped ]; then

echo 'Updating APT'
apt-get -y -qq update > /dev/null 2>&1
echo 'Done.'

echo 'Installing DOTDEB sources'
echo 'deb http://packages.dotdeb.org wheezy all
deb-src http://packages.dotdeb.org wheezy all

deb http://packages.dotdeb.org wheezy-php55 all
deb-src http://packages.dotdeb.org wheezy-php55 all' > /etc/apt/sources.list.d/dotdeb.list

wget -q http://www.dotdeb.org/dotdeb.gpg > /dev/null 2>&1
apt-key add dotdeb.gpg > /dev/null 2>&1
apt-get -y -qq update > /dev/null 2>&1
echo 'Done.'

echo 'Installing base packages'
apt-get -y -qq install git build-essential gettext pkg-config redis-server apache2 apache2-mpm-prefork php5 php5-dev php-pear php5-curl php5-xdebug php5-mysql php5-cli php5-redis > /dev/null 2>&1
echo 'Done.'

echo 'Installing PHPREDMIN'
wget https://github.com/sasanrose/phpredmin/archive/master.zip > /dev/null 2>&1
unzip -qq master.zip > /dev/null 2>&1
mv phpredmin-master /var/www/phpredmin
chown -R vagrant:vagrant /var/www/phpredmin
echo '# phpredmin - Simple web interface to manage and monitor your Redis
#
# Allows only localhost by default
<VirtualHost *:80>
    ServerName redis.mydddblog.test
    DocumentRoot /var/www/phpredmin/public

    <Directory "/var/www/phpredmin/public">
        AllowOverride All
        Order Deny,Allow
        Deny from All
        Allow from 127.0.0.1
        Allow from ::1
        Allow from 172.21.99.1
    </Directory>

    ErrorLog /var/log/apache2/redis.mydddblog.test.log
    CustomLog /var/log/apache2/redis.mydddblog.test.log combined
</VirtualHost>' > /etc/apache2/sites-available/phpredmin
a2ensite phpredmin
echo 'Done.'

echo 'Enabling & Configuring PHP APACHE MODULE'
a2enmod php5 > /dev/null 2>&1
echo 'date.timezone = "Europe/Madrid"' >> /etc/php5/apache2/php.ini
echo 'Done.'

echo 'Configuring Xdebug'
echo ";Debugging
xdebug.remote_enable = 1;
xdebug.remote_connect_back = 1;
xdebug.remote_autostart = 1;
xdebug.remote_port = 9000;

;Profiling
xdebug.profiler_enable = 0;
xdebug.profiler_enable_trigger = 1;
xdebug.profiler_output_dir = \"/tmp/xdebug\";" >> /etc/php5/mods-available/xdebug.ini
echo 'Done.'

echo 'Configuring APACHE HTTPD server'
a2enmod rewrite > /dev/null 2>&1
a2dissite default > /dev/null 2>&1
sed -i 's/export APACHE_RUN_USER=www\-data/export APACHE_RUN_USER=vagrant/g' /etc/apache2/envvars > /dev/null 2>&1
sed -i 's/export APACHE_RUN_GROUP=www\-data/export APACHE_RUN_GROUP=vagrant/g' /etc/apache2/envvars > /dev/null 2>&1
ln -f -s /var/www/cqrs-blog-engine/resources/httpd.conf /etc/apache2/sites-available/mydddblog > /dev/null 2>&1
a2ensite mydddblog > /dev/null 2>&1
chown -R vagrant:vagrant /var/lock/apache2/ > /dev/null 2>&1
echo 'Done.'

echo 'Setting up the application environment'
chown -R vagrant:vagrant var/cache/ > /dev/null 2>&1
echo 'Done.'

touch ~/.machine-bootstraped
fi

echo 'Starting up development services'
service apache2 restart > /dev/null 2>&1
service redis-server restart > /dev/null 2>&1
echo 'Done.'