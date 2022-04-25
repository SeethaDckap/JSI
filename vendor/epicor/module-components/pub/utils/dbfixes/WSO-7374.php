<?php
/**
 * Created Fro Remove Code column from ecc_access_role table
 * Date: 6/14/2019
 * Time: 12:40 PM
 */

require_once('_setup.php');

try
{
    $sql= 'ALTER TABLE ecc_access_role DROP COLUMN code;';
    runQuery(array($sql),$writeConnection);

    echo "<b> 'code' Column Removed From 'ecc_access_role' Successfully.</b>";
}
catch (\Exception $e){
    echo "<b> Exception Occured. Please check is 'code' column already removed from 'ecc_access_role' table.</b>";
}