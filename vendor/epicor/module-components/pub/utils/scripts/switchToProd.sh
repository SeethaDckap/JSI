#!/bin/bash

directory=$1

cd $directory
        echo "3: Sorting file permissions"
        echo

        
        chown -R www-data:www-data $directory
        echo "Done 1/5"
        chmod -R 755 .
        echo "Done 2/5"
        chmod +x $directory/bin/magento
        echo "Done 3/5"
        chmod +x $directory/pub/utils/scripts/*
        echo "Done 4/5"
        chmod -R 775 $directory/pub/media/assets/
bin/magento cache:flush
echo 

chmod -R 777 var/ generated/
rm -rf var/generation/ var/di/ var/view_preprocessed/ var/page_cache
rm -rf var/cache/
rm -rf generated/
rm -rf var/log/production_mode.log
date >> var/log/production_mode.log
bin/magento deploy:mode:set production >> var/log/production_mode.log
chmod -R 777 var/ generated/ pub/
bin/magento cache:disable full_page >> var/log/production_mode.log
bin/magento cache:flush >> var/log/production_mode.log
echo "<hr />"
echo "Set TO <span style="color:red">Production Mode</span>:"