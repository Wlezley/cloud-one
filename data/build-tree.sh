#!/bin/bash

for letterA in {0..9} {a..z}
do
#    rm -rf $letterA
    mkdir -pv $letterA

#    for letterB in {0..9} {a..f}
#    do
#        #echo $letterA/$letterB
#        mkdir -pv $letterA/$letterB
#    done

done

chown -R www-data:www-data ./
echo "DONE!"

#!EOF