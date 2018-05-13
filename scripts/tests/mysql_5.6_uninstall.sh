#!/bin/bash

sudo apt-get remove mysql-common mysql-server-5.6 mysql-server-core-5.6 mysql-client-5.6 mysql-client-core-5.6
sudo apt-get autoremove && sudo apt-get autoclean
