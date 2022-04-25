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

namespace Epicor\Comm\Model\Import;

use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger;

class Handler extends Base
{

    protected $fileName = '/var/log/ecc_stk_debug.log';
    protected $loggerType = Logger::INFO;

}
