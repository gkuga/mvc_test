#!/bin/bash

### User Check ###
USER=`whoami`

if [ "$USER" != 'root' ]; then
  echo "Do as root user again"
  echo "Press Enter to Continue"
  read Enter
  exit 1
else
  echo "you are root"
  echo "Press Enter to Continue"
  read Enter
fi

### User Setings ###
user=atm-user
echo "$user:password::::/home/$user:/bin/bash" | newusers
usermod -a -G wheel $user
echo "%wheel  ALL=(ALL)       NOPASSWD: ALL" | (EDITOR="tee -a" visudo)

### SSH/iptables ###

sed -i -e 's|-A INPUT -m state --state NEW -m tcp -p tcp --dport 22 -j ACCEPT|-A INPUT -m state --state NEW -m tcp -p tcp --dport 60022 -j ACCEPT\n-A INPUT -m state --state NEW -m tcp -p tcp --dport 80 -j ACCEPT|g' /etc/sysconfig/iptables
sed -i -e 's/#Port 22/Port 60022/g' /etc/ssh/sshd_config

### Nginx ###
yum install -y nginx
service nginx start
chkconfig nginx on

### memcached ###
yum install -y memcached
service memcached start
chkconfig memcached on

### PHP ###
yum install -y php-fpm php-cli php-pecl-memcached
sed -i -e 's/user = apache/user = nginx/g' /etc/php-fpm.d/www.conf
sed -i -e 's/group = apache/group = nginx/g' /etc/php-fpm.d/www.conf
chkconfig php-fpm on
service php-fpm start

### php.ini date.timezone = "Asia/Tokyo"
### /etc/nginx/conf.d/default.conf fastcgi_pass   127.0.0.1:9000;
###                                fastcgi_param  SCRIPT_FILENAME  $document_root$fastcgi_script_name;
###                                include        fastcgi_params;

### MySQL ###
sudo yum install -y mysql mysql-server
chkconfig mysqld on
service mysqld start
yum -y install php-pdo php-mysql php-mbstring
cat << _EOT_ > /usr/share/nginx/html/helo.php
<?php
ini_set('display_errors',1);
echo "Hello World!";
phpinfo(); ?>
_EOT_

### Restart Serveces ###
service sshd reload
service iptables restart



