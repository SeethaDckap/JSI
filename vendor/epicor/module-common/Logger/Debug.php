<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

/**
 * AutoSync Logger
 * 
 * @category   Epicor
 * @package    Epicor_Common
 * @author     Epicor Websales Team
 * 
 */

namespace Epicor\Common\Logger;

use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger;


class Debug extends Base
{
    protected $fileName = '/var/log/emptydebug.log';
    protected $loggerType = Logger::DEBUG;

    public function write(array $record) {

    }

}
