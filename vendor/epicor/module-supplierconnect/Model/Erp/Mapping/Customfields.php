<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Model\Erp\Mapping;


class Customfields extends \Epicor\Common\Model\Erp\Mapping\AbstractModel
{

    /**
     * @var \Epicor\Dealerconnect\Model\ResourceModel\EccPacAttributesOption\CollectionFactory
     */
    protected $CustomfieldsFactory;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Epicor\Supplierconnect\Model\ResourceModel\Customfields\CollectionFactory $CustomfieldsFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->CustomfieldsFactory = $CustomfieldsFactory;
        parent::__construct(
            $context,
            $registry,
            $storeManager,
            $resource,
            $resourceCollection,
            $data
        );
    }

    public function _construct()
    {
        parent::_construct();
        $this->_init('Epicor\Supplierconnect\Model\ResourceModel\Customfields');
    }


}
