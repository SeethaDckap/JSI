<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Logger;
/**
 * Elements Payment  
 * 
 * @category    Epicor
 * @package     Epicor_Elements
 * @author      Epicor Web Sales Team
 */

 



class Handler extends \Magento\Framework\Logger\Handler\Base
{
    protected $fileName = '/var/log/supplierremaindercron.log';
    protected $loggerType = \Monolog\Logger::INFO;
}