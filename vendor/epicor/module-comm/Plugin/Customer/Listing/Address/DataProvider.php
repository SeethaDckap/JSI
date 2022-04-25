<?php
/**
 * Copyright © 2010-2019 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Plugin\Customer\Listing\Address;

class DataProvider
{
    /**
     * @var \Magento\Framework\App\RequestInterface $request,
     */
    private $request;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    private $customerFactory;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Address\CollectionFactory
     */
    private $addressCollectionFactory;

    /**
     * DataProvider constructor.
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Customer\Model\ResourceModel\Address\CollectionFactory $addressCollectionFactory
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\ResourceModel\Address\CollectionFactory $addressCollectionFactory
    )
    {
        $this->request = $request;
        $this->customerFactory = $customerFactory;
        $this->addressCollectionFactory = $addressCollectionFactory;
    }

    public function afterGetCollection(
        \Magento\Customer\Ui\Component\Listing\Address\DataProvider $subject,
        $result
    )
    {
        if ($customerId = $this->request->getParam('parent_id')) {
            $customer = $this->customerFactory->create()->load($customerId);
            $erpAccounts = $customer->getErpAcctCounts();
            if (count($erpAccounts) > 1) {
                $collection = $this->addressCollectionFactory->create();
                $collection->addAttributeToSelect('entity_id')
                        ->setCustomerFilter([$customerId])
                        ->addAttributeToFilter('ecc_erp_group_code', ['null' => true]);
                $addressess = $collection->getColumnValues('entity_id');
                if (!empty($addressess)) {
                    $result->addFieldToFilter('entity_id', ['in', $addressess]);

                } else {
                    $result->addFieldToFilter('entity_id', ['null', true]);
                }
            }
        }
        return $result;
    }
}