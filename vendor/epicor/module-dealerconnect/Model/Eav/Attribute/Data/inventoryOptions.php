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
class inventoryOptions extends \Magento\Eav\Model\Entity\Attribute\Source\Boolean
{

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerCustomerFactory;


    public function __construct(
        \Magento\Eav\Model\ResourceModel\Entity\AttributeFactory $eavAttrEntity,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory
    ) {
        $this->_request = $request;
        $this->customerCustomerFactory = $customerCustomerFactory;
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
            $customerId =  (int)$this->_request->getParam('id');
            $customer = $this->customerCustomerFactory->create()->load($customerId);
            $customerType = $customer->getEccErpAccountType();
            $typeLabel = ($customerType == "guest") ? "Global Default" : "ERP Account Default";
            $this->_options = array(
                array(
                    'label' => $typeLabel,
                    'value' => 3
                ),
                array(
                    'label' => __('Own Dealership Only'),
                    'value' => 0
                ),
                array(
                    'label' => __('All Dealership'),
                    'value' => 1
                ),
                array(
                    'label' => __('Dealer Group'),
                    'value' => 2
                )
            );
        }
        return $this->_options;
    }

}
