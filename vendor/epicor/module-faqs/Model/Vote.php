<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Faqs\Model;


/**
 * Faqs Vote model
 * @category   Epicor
 * @package    Faq
 * @author     Epicor Websales Team
 * 
 * @method  __construct()
 */
class Vote extends \Epicor\Database\Model\Faq\Vote
{
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }


    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Epicor\Faqs\Model\ResourceModel\Vote');
    }

}
