<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Logger\DatabaseCopy;

use Monolog\Logger;
use Magento\Framework\Logger\Handler\Base as LoggerHandlerBase;

class Handler extends LoggerHandlerBase
{
    protected $loggerType = Logger::INFO;
    protected $fileName = '/var/log/database_copy.log';
}