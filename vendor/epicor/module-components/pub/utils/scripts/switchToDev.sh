#!/bin/bash

directory=$1

cd $directory

    echo
        chmod -R 777 var/ generated/
        rm -rf var/generation/ var/di/ var/view_preprocessed/ var/page_cache
        rm -rf var/cache/
        rm -rf generated/
        bin/magento cache:flush
        bin/magento deploy:mode:set developer
        chmod -R 777 var/ generated/ pub/
        bin/magento cache:disable full_page
        bin/magento cache:flush
        echo "<hr />"
        echo "5: Set TO <span style="color:red">Developer Mode</span>:"