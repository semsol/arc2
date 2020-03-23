#!/bin/bash

#
# acknowledgement:
# copied from https://github.com/PyMySQL/PyMySQL/blob/master/.travis/initializedb.sh
#

# debug
set -x
# verbose
set -v

if [ ! -z "${DB}" ]; then
    # disable existing database server in case of accidential connection
    sudo service mysql stop

    docker pull ${DB}
    docker run -it --name=mysqld -d -e MYSQL_ALLOW_EMPTY_PASSWORD=yes -p 3306:3306 ${DB}
    sleep 20

    mysql() {
        docker exec mysqld mysql "${@}"
    }
    while :
    do
        sleep 5
        mysql -e 'select version()'
        if [ $? = 0 ]; then
            break
        fi
        echo "server logs"
        docker logs --tail 5 mysqld
    done

    mysql -e 'select VERSION()'

    if [ $DB == 'mysql:8.0' ]; then
        WITH_PLUGIN='with mysql_native_password'
        mysql -e 'SET GLOBAL local_infile=on'
        docker cp mysqld:/var/lib/mysql/public_key.pem "${HOME}"
        docker cp mysqld:/var/lib/mysql/ca.pem "${HOME}"
        docker cp mysqld:/var/lib/mysql/server-cert.pem "${HOME}"
        docker cp mysqld:/var/lib/mysql/client-key.pem "${HOME}"
        docker cp mysqld:/var/lib/mysql/client-cert.pem "${HOME}"
    else
        WITH_PLUGIN=''
    fi

    mysql -uroot -e 'create database testdb DEFAULT CHARACTER SET utf8mb4'
else
    cat ~/.my.cnf

    mysql -e 'select VERSION()'
    mysql -e 'create database testdb DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci;'
fi
