<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Common\Plugin\Customer\Repository;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Framework\Api\ImageProcessorInterface;
use Magento\Customer\Api\CustomerRepositoryInterface as CustomerRepository;

/**
 * Description of CustomerRepositoryPlugin
 *
 *
 */
class CustomerRepositoryPlugin
{
    /**
     * @var \Epicor\Common\Model\CustomerErpaccountFactory
     */
    protected $erpAccountFactory;

    public function __construct(
        \Epicor\Common\Model\CustomerErpaccountFactory $erpAccountFactory
    )
    {
        $this->erpAccountFactory = $erpAccountFactory;
    }

    /**
     * Plugin around customer repository save. If SalesRep is Masquerading then we do not need to Save Customer address for Sales customer account.
     *
     * @param CustomerRepository $subject
     * @param \Closure $proceed
     * @param CustomerInterface $customer
     * @param null $passwordHash
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */

    public function aroundSave(
        CustomerRepository $subject,
        \Closure $proceed,
        \Magento\Customer\Api\Data\CustomerInterface $customer,
        $passwordHash = null)
    {
        /** @var CustomerInterface $savedCustomer */
        try {
            $extensionAttributes = $customer->getExtensionAttributes();
            /** get current extension attributes from entity **/
            if($extensionAttributes && $extensionAttributes->getEccMultiErpId()){
                $data = [
                    'erp_account_id' => $extensionAttributes->getEccMultiErpId(),
                    'customer_id' => $customer->getId(),
                    'erp_account_type' => $extensionAttributes->getEccMultiErpType(),
                    'contact_code' => $extensionAttributes->getEccMultiContactCode()];
                $this->erpAccountFactory->create()->setData($data)->saveRel();
            }
        } catch (\Exception $e) {
            die($e->getMessage());
        }
        return $proceed($customer, $passwordHash);
    }
}
