<?php

ini_set('memory_limit', '1G');

$cmd = '/bin/bash ' . __DIR__ . '/scripts/gitupdatedp.sh ' . __DIR__ . "/../../";

while (@ ob_end_flush()); // end all output buffers if any

$proc = popen($cmd, 'r');
echo '<pre>';
while (!feof($proc))
{
    echo fread($proc, 4096);
    @ flush();
}
echo '</pre>';