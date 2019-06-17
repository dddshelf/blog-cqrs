CQRS + ES Blog Engine
=====================

A blog engine written in PHP and powered with CQRS + Event Sourcing

## Requirements

All you need to run this application is Vagrant.

**http://www.vagrantup.com/**

## Installation

```bash
git clone https://github.com/theUniC/blog-cqrs.git
cd blog-cqrs
wget http://getcomposer.org/composer.phar
php composer.phar install
```

Next you have to update your hosts file (usually located at */etc/hosts*), with the line below

    172.21.99.6 mydddblog.test www.mydddblog.test redis.mydddblog.test
    
## Running the application via Vagrant

From the root application folder, run

    vagrant up --provision
    
When vagrant finishes bootstraping the VM, open up a browser and go to

**http://www.mydddblog.test**

Have fun!

## Running the application via Docker

Create docker-compose.yml from docker-compose.yml.dist

    cp docker-compose.yml.dist docker-compose.yml

Create docker/nginx/default.conf from docker/nginx/default.conf.dist

    cp docker/nginx/default.conf.dist docker/nginx/default.conf

Create config/depended.php from config/depended.php.dist

    cp config/depended.php.dist config/depended.php