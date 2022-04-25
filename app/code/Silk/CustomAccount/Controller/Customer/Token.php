<?php

namespace Silk\CustomAccount\Controller\Customer;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Context;
use Magento\Integration\Model\Oauth\TokenFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Epicor\Comm\Helper\Data as EpicorHelper;
use \Magento\Framework\App\Config\ScopeConfigInterface;

class Token extends \Magento\Framework\App\Action\Action
{
    protected $customerSession;

    protected $tokenModelFactory;

    protected $resultJsonFactory;

    protected $epicorHelper;

    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        TokenFactory $tokenModelFactory,
        JsonFactory $resultJsonFactory,
        EpicorHelper $epicorHelper,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->customerSession = $customerSession;
        $this->tokenModelFactory = $tokenModelFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->epicorHelper = $epicorHelper;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
    }


    public function execute()
    {
        try {
            if ($this->customerSession->isLoggedIn()) {
                $customerId = $this->customerSession->getCustomerId();
                $token = $this->tokenModelFactory->create();
                $tokenKey = $token->createCustomerToken($customerId)->getToken();
                $erpAccount = $this->epicorHelper->getErpAccountInfo();
                $erpCustomerId = null;
                $company = null;
                if($erpAccount){
                    $erpCustomerId = $erpAccount->getAccountNumber();
                    $company = $erpAccount->getCompany();
                }

                $useCustomAPI = $this->scopeConfig->getValue('customapi/switch/enable', 
    \Magento\Store\Model\ScopeInterface::SCOPE_STORE);


                $response = [
                    'token' => $tokenKey,
                    'erp_customer_id' => $erpCustomerId,
                    'erp_company' => $company,
                    'use_custom_api' => $useCustomAPI
                ];
            }
            else{
                $response = [
                    'token' => null,
                    'erp_customer_id' => null,
                    'erp_company' => null,
                    'use_custom_api' => false
                ];
            }
        }
        catch (\Exception $e) {
            $response = [
                'status' => 'error',
                'error' => $e->getMessage(),
                'token' => null,
                'erp_customer_id' => null,
                'erp_company' => null,
                'use_custom_api' => false
            ];
        }

        

        return $this->resultJsonFactory->create()->setData($response);
    }
}
