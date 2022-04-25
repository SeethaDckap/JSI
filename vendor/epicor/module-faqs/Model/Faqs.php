<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Faqs\Model;



/**
 * Faqs item model
 * @category   Epicor
 * @package    Faq
 * @author     Epicor Websales Team
 * 
 * @method  __construct()
 */
class Faqs extends \Epicor\Database\Model\Faq
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
        $this->_init('Epicor\Faqs\Model\ResourceModel\Faqs');
    }

    /**
     * If object is new adds creation date
     *
     * @return \Epicor\Faqs\Model\Faqs
     */
    public function beforeSave()
    {
        parent::beforeSave();
        if ($this->isObjectNew()) {
            //M1 > M2 Translation Begin (Rule 25)
            //$this->setData('created_at', Varien_Date::now());
            $this->setData('created_at', date('Y-m-d H:i:s'));
            //M1 > M2 Translation End
        }
        return $this;
    }

}
