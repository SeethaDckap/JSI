<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\ResourceModel\Syn;


/**
 * Syn Log model
 * 
 * @category   Epicor
 * @package    Epicor_License
 * @author     Epicor Websales Team
 */
class Log extends \Epicor\Database\Model\ResourceModel\Syn\Log
{
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        $connectionName = null
    ) {
        parent::__construct(
            $context,
            $connectionName
        );
    }


}
