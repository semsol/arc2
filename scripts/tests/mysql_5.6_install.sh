#!/bin/bash

sudo apt-get install libaio1
wget -O mysql-5.6.39.deb http://dev.mysql.com/get/Downloads/MySQL-5.6/mysql-5.6.39-debian6.0-x86_64.deb/from/http://cdn.mysql.com/
sudo dpkg -i mysql-5.6.39.deb
sudo cp /opt/mysql/server-5.6/support-files/mysql.server /etc/init.d/mysql.server
sudo ln -s /opt/mysql/server-5.6/bin/* /usr/bin/

# some config values were changed since 5.5
sudo sed -i'' 's/table_cache/table_open_cache/' /etc/mysql/my.cnf
sudo sed -i'' 's/log_slow_queries/slow_query_log/' /etc/mysql/my.cnf
sudo sed -i'' 's/basedir[^=]\\+=.*$/basedir = \\/opt\\/mysql\\/server-5.6/' /etc/mysql/my.cnf
sudo /etc/init.d/mysql.server start
