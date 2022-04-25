<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

/**
 * Elements Payment  
 * 
 * @category    Epicor
 * @package     Epicor_Elements
 * @author      Epicor Web Sales Team
 */

 
namespace Epicor\Esdm\Logger;


class Handler extends \Magento\Framework\Logger\Handler\Base
{
    protected $fileName = '/var/log/esdm.log';
    protected $loggerType = \Monolog\Logger::INFO;
}