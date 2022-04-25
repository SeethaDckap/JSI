<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Customer;

use Magento\Backend\App\Action\Context;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Controller\ResultFactory;

class MassAssignAccount extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\Customer
{

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $collectionFactory;

    /**
     * @var \Epicor\Common\Model\CustomerErpaccountFactory
     */
    protected $erpAccountFactory;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory,
        \Epicor\Common\Model\CustomerErpaccountFactory $erpAccountFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\Registry $registry
    ) {
        parent::__construct($context, $backendAuthSession);

        $this->backendSession = $context->getSession();
        $this->collectionFactory = $customerCustomerFactory;
        $this->erpAccountFactory = $erpAccountFactory;
        $this->customerRepository = $customerRepository;
        $this->registry = $registry;
    }
    
    public function execute()
    {
        $data = $this->getRequest()->getParams();
        $customersUpdated = 0;
        $multipleErpCounts = 0;
        if ($data) {
            $customersIds = $this->getRequest()->getParam('customer');
             if (!is_array($customersIds)) {
                $this->messageManager->addError(__('Please select customer1(s).'));
            } else {
                $accountType = $data['ecc_erp_account_type'];
                if($accountType != 'guest'){
                    $customerField = $data[$accountType . '_field'];
                    $accountId = $data[$customerField];
                }else{
                   $accountType ='guest'; 
                   $customerField ='guest';
                   $accountId =0;
                }
                try {
                    foreach ($customersIds as $customerId) {
                        $customer = $this->collectionFactory->create()->load($customerId);
                        $erpCount = $customer->getErpAcctCounts();
                        if(!empty($erpCount) && count($erpCount) > 1){
                            $multipleErpCounts++;
                            continue;
                        }
                        $erpAcctCounts = $customer->getErpAcctCounts();
                        if(empty($erpAcctCounts) || (!empty($erpAcctCounts) && count($erpAcctCounts) == 1)){
                            if(!empty($accountId) && $accountType === 'customer' && !empty($erpAcctCounts)){
                                //update existing ERP Account > ERP Account
                                $data = [
                                    'erp_account_id' => $accountId,
                                    'customer_id' => $customer->getId(),
                                    'erp_account_type' => $accountType
                                    ];

                                $customer->setData('ecc_erpaccount_id', $accountId);
                                $this->erpAccountFactory->create()->setData($data)->updateByCustomerId();
                            }elseif(empty($erpAcctCounts) && $accountType === 'customer' && !empty($accountId)){
                                //update existing Guest/Salesrep/Supplier > ERP Account
                                $customerRepository = $this->customerRepository->getById($customer->getId());
                                $extensionAttributes = $customerRepository->getExtensionAttributes(); /** get current extension attributes from entity **/
                                $extensionAttributes->setEccMultiErpId($accountId);
                                $extensionAttributes->setEccMultiErpType('customer');
                                $customerRepository->setExtensionAttributes($extensionAttributes);
                                $this->customerRepository->save($customerRepository);
                                $customer->setEccErpAccountType($accountType);
                                $customer->setData($data['salesrep_field'], 0);
                                $customer->setData($data['supplier_field'], 0);
                                $customer->setData('ecc_erpaccount_id', $accountId);
                            }elseif (in_array($accountType, ['guest', 'salesrep', 'supplier'])){
                                //update existing  ERP Account  > Guest/Salesrep/Supplier
                                if(!empty($erpAcctCounts)){
                                    $erpIdToDel = $erpAcctCounts[0]['erp_account_id'];
                                    $customer->deleteErpAcctById($erpIdToDel);
                                }
                                $customer->setEccErpAccountType($accountType);
                                $customer->setData($customerField, $accountId);
                            }
                        }
                        $customer->save();
                        $this->_eventManager->dispatch('ecc_cuco_save_after', ['customer' => $customer]);
                        $this->registry->unregister('updating_erp_address');
                        $customersUpdated++;
                    }
                    if($multipleErpCounts > 0){
                        $this->messageManager->addErrorMessage(
                            __('Total of %1 record(s) could not be updated. Customer(s) selected are mapped to more than 1 ERP Account and this action is not permitted.', $multipleErpCounts)
                        );
                    }
                    if($customersUpdated > 0){
                        $this->messageManager->addSuccess(
                            __('Total of %1 record(s) were updated.', $customersUpdated)
                        );
                    }
                } catch (\Exception $e) {
                    $this->messageManager->addError($e->getMessage());
                }
            }
        } else {
            $this->messageManager->addError(__('Please select customer(s).'));
        }

        $this->_redirect('customer/index/index');
        return;
    }

    }
