<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

/**
 * AutoSync Logger Handler
 * 
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 * 
 */

namespace Epicor\Comm\Logger\Autosync;

use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger;

class Handler extends Base
{

    protected $fileName = '/var/log/ecc_autosync.log';
    protected $loggerType = Logger::INFO;

}
