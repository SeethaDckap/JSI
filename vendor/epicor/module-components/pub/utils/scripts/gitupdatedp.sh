#!/bin/bash

currentbranch="dealersportal"
eccqapass=Violin1234!

directory=$1

cd $directory

echo "1: Updating git cache for update check"
echo

echo "ssh -o StrictHostKeyChecking=no \$@;" > /tmp/sshOverride.bash;
chmod 700 /tmp/sshOverride.bash;
export GIT_SSH=/tmp/sshOverride.bash

sshpass -p $eccqapass git fetch

olddate=`git log -1 --pretty=%at`
newdate=`git log origin/master -1 --pretty=%at`

echo "LOCAL LAST COMMIT TIMESTAMP: $olddate";
echo "REMOTE LAST COMMIT TIMESTAMP: $newdate";

if [ "$olddate" -ne "$newdate" ];then

        echo "<hr />"
        echo "2: Update Required, performing git updates"
        echo

        sshpass -p $eccqapass git fetch origin $currentbranch
        sshpass -p $eccqapass git reset --hard FETCH_HEAD 
        sshpass -p $eccqapass git clean -df

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

        echo "Running bin/magento setup:upgrade"
        echo "=============================================================================================================" > var/log/gitupdate.log
        date >> var/log/gitupdate.log
        echo "=============================================================================================================" >> var/log/gitupdate.log
        bin/magento setup:upgrade >> var/log/gitupdate.log
        echo "Running bin/magento setup:static-content:deploy THIS MAY TAKE SOME TIME"
        bin/magento setup:static-content:deploy >> var/log/gitupdate.log
        echo "Running bin/magento cache:flush"
        bin/magento cache:flush >> var/log/gitupdate.log

        echo "<hr />"
        echo "5: Update complete, change log since last upgrade:"
        echo
        git log --since $olddate
else
        echo "<hr />"
        echo "2: No update required, Last log:"
        echo
        
        git log -1
fi


