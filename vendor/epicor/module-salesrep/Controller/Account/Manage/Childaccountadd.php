<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Controller\Account\Manage;

class Childaccountadd extends \Epicor\SalesRep\Controller\Account\Manage
{

    /**
     * @var \Epicor\SalesRep\Helper\Account\Manage
     */
    protected $salesRepAccountManageHelper;

    /**
     * @var \Epicor\SalesRep\Model\AccountFactory
     */
    protected $salesRepAccountFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;
    
    public function __construct(
         \Epicor\SalesRep\Controller\Context $context,    
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->salesRepAccountManageHelper = $context->getSalesRepAccountManageHelper(); //$salesRepAccountManageHelper;
        $this->salesRepAccountFactory = $context->getSalesRepAccountFactory(); //$salesRepAccountFactory;
        $this->scopeConfig = $scopeConfig;
        $this->logger = $context->getLogger();
        
        parent::__construct($context);
    }
    public function execute()
    {
        $helper = $this->salesRepAccountManageHelper;
        /* @var $helper Epicor_SalesRep_Helper_Account_Manage */
        //$this->salesRepAccountManageHelper->getManagedSalesRepAccount()->getName()
        $salesRepAccount = $helper->getManagedSalesRepAccount();
        /* @var $salesRepAccount Epicor_SalesRep_Model_Account */

        $data = $this->getRequest()->getPost();

        if ($data && $helper->canAddChildrenAccounts()) {

            try {
                $child = $this->salesRepAccountFactory->create()->load($data['child_sales_rep_account_id'], 'sales_rep_id');
                /* @var $child Epicor_SalesRep_Model_Account */

                $error = '';
                $msg = '';

                if (!$child->isObjectNew()) {
                    if ($this->scopeConfig->getValue('epicor_salesrep/management/frontend_children_addexisting', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
                        if ($child->getId() == $salesRepAccount->getId() || $child->hasChildAccount($salesRepAccount->getId())) {
                            $error = __('Existing Sales Rep Account Found. Cannot assign as a Children due Hierarchy Loop');
                        } else if (in_array($child->getId(), $salesRepAccount->getChildAccountsIds())) {
                            $error = __('Existing Sales Rep Account Found. Account is already a Child');
                        } else {
                            $salesRepAccount->addChildAccount($child->getId());
                            $salesRepAccount->save();
                            $msg = __('Existing Sales Rep Account Found. It has been updated to be a Children for %1', $salesRepAccount->getName());
                        }
                    } else {
                        $error = __('Existing Sales Rep Account Found. Cannot create this Account');
                    }
                } else {
                    $child->setCompany($salesRepAccount->getCompany());
                    $child->setSalesRepId($data['child_sales_rep_account_id']);
                    $child->setName($data['child_sales_rep_account_name']);
                    $child->setCatalogAccess($salesRepAccount->getCatalogAccess());
                    $child->save();
                    $salesRepAccount->addChildAccount($child->getId());
                    $salesRepAccount->save();
                    //M1 > M2 Translation Begin (Rule 55)
                    //$msg = $this->__('New Sales Rep Account Created. It has been assigned to be a Children for %s', $salesRepAccount->getName());
                    $msg = __('New Sales Rep Account Created. It has been assigned to be a Children for %1', $data['child_sales_rep_account_name']);
                    //M1 > M2 Translation End
                }
            } catch (Exception $ex) {
                $this->logger->critical($ex);
                $error = __('An error occured, please try again');
            }
            
            if (!empty($error)) {
                $this->messageManager->addErrorMessage($error);
            } else {
                $this->messageManager->addSuccessMessage($msg);
            }
        }

        $this->_redirect('*/*/hierarchy');
    }

    }
