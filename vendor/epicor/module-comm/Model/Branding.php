<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model;


/**
 * @method setCompany(string $company)
 * @method string getCompany()
 * @method setSite(string $site)
 * @method string getSite()
 * @method setWarehouse(string $warehouse)
 * @method string getWarehouse()
 * @method setGroup(string $group)
 * @method string getGroup()
 */
class Branding extends \Magento\Framework\Model\AbstractModel
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


    protected function _construct()
    {
        
    }

}
