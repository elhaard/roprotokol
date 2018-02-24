#!/bin/bash

# Bash script to start mariadb in docker.
# Run as root
#
# Before running, please pull docker image:
#
#   docker pull mariadb
#



if [ -f /etc/init.d/mysql ]; then
    if /etc/init.d/mysql status | grep -q "start/running"; then
	echo "Stopping MySql"

	/etc/init.d/mysql stop
        sleep 5
    else
	echo "Mysql not running. Good."
    fi
fi

# Make sure that the socket can be written even if user ID's do not match
chmod a+w /var/run/mysqld

DATADIR="/var/lib/mariadb"

PARMS=()
PARMS+=('run')
PARMS+=('-d')
PARMS+=('--name mariadb-roprotokol')

PARMS+=("-v ${DATADIR}:/var/lib/mysql")
PARMS+=("-v /var/run/mysqld:/var/run/mysqld")
PARMS+=("-P")

if [ ! -d $DATADIR ]; then
    echo "Creating directory ${DATADIR}"
    mkdir -p $DATADIR

    PARMS+=('-e MYSQL_ROOT_PASSWORD=roprotokol')
else
    echo "Using existing database"
fi

PARMS+=('mariadb:latest')

echo "Starting mariadb in Docker..."

docker ${PARMS[@]}


echo "Done"
