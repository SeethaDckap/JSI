#!/bin/bash

# Ensure correct remote is set.
git remote set-url origin https://epicor-corp:3gdhayz5otyo2twjd44dvh6juvphnb3ejzbucfv2sgfhyji2htpa@epicor-corp.visualstudio.com/DefaultCollection/Epicor-PD/_git/ecc-m2-dev

currentbranch=$(git branch | sed -n -e 's/^\* \(.*\)/\1/p')

directory=$1

cd $directory

echo "1: Updating git cache for update check"

echo "ssh -o StrictHostKeyChecking=no \$@;" > /tmp/sshOverride.bash;
chmod 700 /tmp/sshOverride.bash;
export GIT_SSH=/tmp/sshOverride.bash

git fetch

olddate=`git log -1 --pretty=%at`
newdate=`git log origin/master -1 --pretty=%at`

echo "Current Branch: $currentbranch";

echo "LOCAL LAST COMMIT TIMESTAMP: $olddate";
echo "REMOTE LAST COMMIT TIMESTAMP: $newdate";

if [ "$olddate" -ne "$newdate" ];then

        function version { printf "%03d%03d%03d%03d" $(echo "$1" | tr '.' ' '); }
        currentversion=$(php bin/magento --version|awk 'NF>1{print $NF}')

        echo "<hr />"
        echo "2: Update Required, performing git updates"
        echo

        git fetch origin $currentbranch
        git reset --hard FETCH_HEAD
        git clean -df

        versioncompare=2.3.0
        if [ $(version $currentversion) -gt $(version $versioncompare) ]; then
             echo "Magento version is greater then 2.3.0"
        else
             echo "Magento version is less then or equal to 2.3.0 - removing elasticsearch module"
             rm -rf $directory/app/code/Epicor/Elasticsearch/
             echo "Magento version is less then or equal to 2.3.0 - removing recaptcha module"
             rm -rf $directory/app/code/Epicor/Recaptcha/
        fi

        newversion=2.4.0
        if [ $(version $currentversion) -ge $(version $newversion) ]; then
            echo "Magento version is greater than or equal to 2.4.0"
            echo "Magento version is greater than or equal to 2.4.0 - removing Authorizenet module"
            rm -rf $directory/app/code/Epicor/Authorizenet/
            echo "Magento version is greater than or equal to 2.4.0 - removing Recaptcha module"
            rm -rf $directory/app/code/Epicor/Recaptcha/
            echo "Magento version is greater than or equal to 2.4.0 - removing Mysqlsearch module"
            rm -rf $directory/app/code/Epicor/Mysqlsearch/
            echo "Magento version is greater than or equal to 2.4.0 - removing MspTfa module"
            rm -rf $directory/app/code/Epicor/MspTfa/
        elif [ $(version $currentversion) -lt $(version $newversion) ]; then
            echo "Magento version is less than 2.4.0"
            echo "Magento version is less than 2.4.0 - removing Elasticsearch7 module"
            rm -rf $directory/app/code/Epicor/Elasticsearch7/
            echo "Magento version is less than 2.4.0 - removing GoogleRecaptcha module"
            rm -rf $directory/app/code/Epicor/GoogleRecaptcha/
            echo "Magento version is less than 2.4.0 - removing Tfa module"
            rm -rf $directory/app/code/Epicor/Tfa/
        fi

        echo "<hr />"
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

        echo "<hr />"
        echo "4: Running Magento Upgrade commands"
        echo

        rm -rf var/generation/ var/view_preprocessed/
        rm -rf pub/static/frontend pub/static/adminhtml
        rm -rf var/cache/
        rm -rf generated/

        echo "Running bin/magento setup:upgrade"
        echo "=============================================================================================================" > var/log/gitupdate.log
        date >> var/log/gitupdate.log
        echo "=============================================================================================================" >> var/log/gitupdate.log
        bin/magento setup:upgrade >> var/log/gitupdate.log
        echo "Running bin/magento setup:static-content:deploy THIS MAY TAKE SOME TIME"
        bin/magento setup:static-content:deploy >> var/log/gitupdate.log
        echo "Running bin/magento cache:flush"
        bin/magento cache:flush >> var/log/gitupdate.log
        bin/magento deploy:mode:set developer

        echo "<hr />"
        echo "5: Update complete,Set To <span style="color:red">DEVELOPER Mode</span> change log since last upgrade:"
        echo
        git log --since $olddate
else
        echo "<hr />"
        echo "2: No update required, Last log:"
        echo
        
        git log -1
fi


