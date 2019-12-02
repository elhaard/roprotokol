#!/bin/sh
mysqldump -u roprotokol roprotokol -p -e -c --no-create-info --tables reservation Boat BoatCategory BoatRights BoatType Damage Destination Locations MemberRightType TripRights TripType boat_brand boat_usage rights_subtype> data.sql

mysqldump -u roprotokol roprotokol -p -e -c --no-create-info --tables MemberRightType > MemberRightTypes.sql
