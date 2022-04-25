<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Cre\Logger;
/**
 * Elements Cre  
 * 
 * @category    Epicor
 * @package     Epicor_Cre
 * @author      Epicor Web Sales Team
 */

 



class Handler extends \Magento\Framework\Logger\Handler\Base
{
    protected $fileName = '/var/log/cre.log';
    protected $loggerType = \Monolog\Logger::INFO;
}