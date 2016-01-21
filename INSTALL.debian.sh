#!/bin/sh
#apt-get install python-mysqldb  php5-mysqlnd mysql nodejs npm

mysql -u roprotokol --password=roprotokol roprotokol<<EOSQL
  DROP  DATABASE IF EXISTS roprotokol;
  CREATE SCHEMA roprotokol DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
  CREATE USER 'roprotokol'@'localhost' IDENTIFIED BY 'roprotokol';
  GRANT ALL PRIVILEGES ON 'roprotokol'.* TO 'roprotokol'@'localhost';
  FLUSH PRIVILEGES;
EOSQL


echo NOW mkdb.sql
mysql -u roprotokol -p'roprotokol' roprotokol < backend/convert/mkdb.sql
# mysql -u roprotokol -p'roprotokol' roprotokol <  backend/convert/queries.sql


echo 'DBCMD="mysql -u roprotokol --password=roprotokol roprotokol"' >  backend/convert/secret.sh
echo "roprotokol" > backend/tests/secret.db

echo NOW FAKE
./backend/convert/import.sh fake

echo now configure your webserver to server DSR roprotokol
