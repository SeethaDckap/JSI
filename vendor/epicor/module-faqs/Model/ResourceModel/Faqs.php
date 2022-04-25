<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Faqs\Model\ResourceModel;

/**
 * Faqs item resource model
 * @category   Epicor
 * @package    Faq
 * @author     Epicor Websales Team
 * 
 * @method   __construct()
 */
class Faqs extends \Epicor\Database\Model\ResourceModel\Faq
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
