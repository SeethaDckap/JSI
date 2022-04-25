<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Model;

use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger;

class Log extends Base
{
    protected $fileName = '/var/log/epicor_common.log';
    protected $loggerType = Logger::DEBUG;
}