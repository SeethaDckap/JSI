<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Config\Backend;


/**
 * Default Erp account backend controller
 * 
 * Updates the Default ERP code if the Erp account changes
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Brandimage extends \Magento\Framework\App\Config\Value
{
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $resource,
            $resourceCollection,
            $data
        );
    }


    public function beforeSave()
    {
        $value = $this->getValue();
        if ($value) {
            $intValue = (int) $value;
            if (!is_int($intValue)) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Size of Brand Image must be an integer'));
            }
        }
        parent::beforeSave();
    }

}
