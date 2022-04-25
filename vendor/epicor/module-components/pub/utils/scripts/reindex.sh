#!/bin/bash

directory=$1

cd $directory

bin/magento indexer:reset    
bin/magento indexer:reindex
