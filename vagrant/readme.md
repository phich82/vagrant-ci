# How To Install PHP 7.3, 7.2, 7.1 on CentOS/RHEL 7.6

# 1. Setup Yum Repository
	$ sudo yum install epel-release

	$ sudo rpm -Uvh http://rpms.famillecollet.com/enterprise/remi-release-7.rpm

# 2. Install PHP 7 on CentOS

## Install PHP 7.3 
	$ sudo yum --enablerepo=remi-php73 install php

## Install PHP 7.2 
	$ sudo yum --enablerepo=remi-php72 install php

## Install PHP 7.1 
	$ sudo yum --enablerepo=remi-php71 install php

## Check the php version
	$ php -v

## Install PHP Modules

### For PHP 7.3
	$ sudo yum --enablerepo=remi-php73 install pdo-php php-mysql php-pgsql php-opcache php-xml php-soap php-xmlrpc php-mbstring php-json php-gd php-mcrypt

### For PHP 7.2
	$ sudo yum --enablerepo=remi-php72 install pdo-php php-mysql php-pgsql php-opcache php-xml php-soap php-xmlrpc php-mbstring php-json php-gd php-mcrypt

### For PHP 7.1
	$ sudo yum --enablerepo=remi-php71 install pdo-php php-mysql php-pgsql php-opcache php-xml php-soap php-xmlrpc php-mbstring php-json php-gd php-mcrypt

## Check the installed modules
	$ php --modules
	
## Find the path to php.ini
	$php --ini | grep "Loaded Configuration File"

# II. Install Apache Web Server
	sudo yum -y update
	sudo yum install httpd

	sudo systemctl start httpd
	sudo systemctl enable httpd
	sudo systemctl status httpd
	sudo systemctl restart httpd.service

# III. How to Start and Enable Firewalld on CentOS 7:
1. Install
	sudo yum install firewalld

2. Enable/disable Firewalld (when OS starting)
	sudo systemctl enable firewalld
	sudo systemctl disable firewalld

3. Start/stop Firewalld
	sudo systemctl start firewalld
	sudo systemctl stop firewalld

4. Check the Status of Firewalld
	sudo systemctl status firewalld

5. Remove port:
	sudo firewall-cmd --permanent --remove-port=443/tcp

6. Reload
	sudo systemctl reload firewalld

# NOTES: When connect to database from vagrant
1. Check vagrant_private_key of the using box by command: vagrant ssh-config

CentOS:
	sudo vi /etc/httpd/conf/httpd.conf
