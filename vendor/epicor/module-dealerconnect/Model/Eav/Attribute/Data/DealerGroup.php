<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Model\Eav\Attribute\Data;


/**
 *
 * @category   Epicor
 * @package    Epicor_Dealerconnect
 * @author     Epicor Websales Team
 */
class DealerGroup extends \Magento\Eav\Model\Entity\Attribute\Source\Boolean
{

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerCustomerFactory;

    /**
     * @var \Epicor\Dealerconnect\Model\ResourceModel\Dealergroups\CollectionFactory
     */
    protected $dealerGroupsResourceModelCollectionFactory;


    public function __construct(
        \Magento\Eav\Model\ResourceModel\Entity\AttributeFactory $eavAttrEntity,
        \Magento\Framework\App\RequestInterface $request,
        \Epicor\Dealerconnect\Model\ResourceModel\Dealergroups\CollectionFactory $dealerGroupsResourceModelCollectionFactory,
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory
    ) {
        $this->_request = $request;
        $this->customerCustomerFactory = $customerCustomerFactory;
        $this->dealerGroupsResourceModelCollectionFactory = $dealerGroupsResourceModelCollectionFactory;

        parent::__construct(
            $eavAttrEntity
        );
    }


    /**
     * Retrieve all options array
     *
     * @return array
     */
    public function getAllOptions()
    {
        if (is_null($this->_options)) {
            $arr = array();
            $customerId =  (int)$this->_request->getParam('id');
            $customer = $this->customerCustomerFactory->create()->load($customerId);
            $customerType = $customer->getEccErpAccountType();
            $typeLabel = ($customerType == "guest") ? "Global Default" : "ERP Account Default";
            $collection = $this->dealerGroupsResourceModelCollectionFactory->create();
            $collection->filterActive();
            $arr[] =  array('label' => $typeLabel, 'value' => 0);
            foreach($collection as $group){
                $arr[] = array('label' => $group->getTitle(), 'value' => $group->getId());
            }
            $this->_options = $arr;
        }
        return $this->_options;
    }

}
