<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Helper;


class Messaging extends \Epicor\Comm\Helper\Messaging
{
    const XML_PATH_EPICOR_QUOTES_GENERAL_QUOTE_STATUS = 'epicor_quotes/general/select_quote_status';

    /**
     * @var \Epicor\Customerconnect\Model\Erp\Mapping\InvoicestatusFactory
     */
    protected $customerconnectErpMappingInvoicestatusFactory;

    /**
     * @var \Epicor\Customerconnect\Model\Erp\Mapping\ErpquotestatusFactory
     */
    protected $customerconnectErpMappingErpquotestatusFactory;

    /**
     * @var \Epicor\Customerconnect\Model\Erp\Mapping\RmastatusFactory
     */
    protected $customerconnectErpMappingRmastatusFactory;

    /**
     * @var \Epicor\Customerconnect\Model\Erp\Mapping\ServicecallstatusFactory
     */
    protected $customerconnectErpMappingServicecallstatusFactory;

    /**
     * @var \Epicor\Customerconnect\Model\Erp\Mapping\ReasoncodeFactory
     */
    protected $customerconnectErpMappingReasoncodeFactory;
    
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    public function __construct(
        \Epicor\Comm\Helper\Messaging\Context $context,
        \Epicor\Customerconnect\Model\Erp\Mapping\InvoicestatusFactory $customerconnectErpMappingInvoicestatusFactory,
        \Epicor\Customerconnect\Model\Erp\Mapping\ErpquotestatusFactory $customerconnectErpMappingErpquotestatusFactory,
        \Epicor\Customerconnect\Model\Erp\Mapping\RmastatusFactory $customerconnectErpMappingRmastatusFactory,
        \Epicor\Customerconnect\Model\Erp\Mapping\ServicecallstatusFactory $customerconnectErpMappingServicecallstatusFactory,
        \Epicor\Customerconnect\Model\Erp\Mapping\ReasoncodeFactory $customerconnectErpMappingReasoncodeFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    )
    {
        $this->customerconnectErpMappingInvoicestatusFactory = $customerconnectErpMappingInvoicestatusFactory;
        $this->customerconnectErpMappingErpquotestatusFactory = $customerconnectErpMappingErpquotestatusFactory;
        $this->customerconnectErpMappingRmastatusFactory = $customerconnectErpMappingRmastatusFactory;
        $this->customerconnectErpMappingServicecallstatusFactory = $customerconnectErpMappingServicecallstatusFactory;
        $this->customerconnectErpMappingReasoncodeFactory = $customerconnectErpMappingReasoncodeFactory;
        $this->productRepository = $productRepository;
        $this->dataObjectFactory = $context->getDataObjectFactory();
        parent::__construct($context);
    }

    public function getPermissionFor($customer)
    {
        return true;
    }

    /**
     * Return the ERP invoice state / status for the given erp code.
     * 
     * @param string $erpCode
     * 
     * @return \Epicor\Customerconnect\Model\Erp\Mapping\Invoicestatus
     */
    public function getInvoiceStatusMapping($erpCode)
    {

        $model = $this->customerconnectErpMappingInvoicestatusFactory->create();
        $erp = $model->loadMappingByStore($erpCode, 'code');

        return $erp;
    }

    /**
     * Gets the invoice status description from its ERP code
     * 
     * @param string $code
     * @param string $description
     * 
     * @return string
     */
    public function getInvoiceStatusDescription($code, $description = '')
    {
        $erp = $this->getInvoiceStatusMapping($code);
        if ($erp->getStatus())
            $description = $erp->getStatus();
        if ($description == '')
            $description = $code;
        return $description;
    }

    /**
     * Return the ERP invoice state / status for the given erp code.
     * 
     * @param string $erpCode
     * 
     * @return \Epicor\Customerconnect\Model\Erp\Mapping\Erporderstatus
     */
    public function getErporderStatusMapping($erpCode)
    {
        $model = $this->customerconnectErpMappingErporderstatusFactory->create();
        $erp = $model->loadMappingByStore($erpCode, 'code');

        return $erp;
    }

    /**
     * Gets the invoice status description from its ERP code
     * 
     * @param string $code
     * @param string $description
     * 
     * @return string
     */
    public function getErporderStatusDescription($code, $description = '')
    {
        $erp = $this->getErporderStatusMapping($code);
        if ($erp->getStatus())
            $description = $erp->getStatus();
        if ($description == '')
            $description = $code;
        return $description;
    }

    /**
     * Return the ERP quote state / status for the given erp code.
     * 
     * @param string $erpCode
     * 
     * @return \Epicor\Customerconnect\Model\Erp\Mapping\Erpquotestatus
     */
    public function getErpquoteStatusMapping($erpCode)
    {
        /* @var $model Epicor_Customerconnect_Model_Erp_Mapping_Erpquotestatus */
        $model = $this->customerconnectErpMappingErpquotestatusFactory->create();
        $erp = $model->loadMappingByStore($erpCode, 'code');

        return $erp;
    }

    /**
     * Gets the quote status description from its ERP code
     * 
     * @param string $code
     * @param string $description
     * 
     * @return string
     */
    public function getErpquoteStatusDescription($code, $description = '', $return = 'status')
    {
        $erp = $this->getErpquoteStatusMapping($code);
        if ($erp->getStatus())
            $description = $erp->getData($return);
        if ($description == '')
            $description = $code;
        return $description;
    }

    /**
     * Return the ERP rma state / status for the given erp code.
     * 
     * @param string $erpCode
     * 
     * @return \Epicor\Customerconnect\Model\Erp\Mapping\Rmastatus
     */
    public function getRmaStatusMapping($erpCode, $storeId = null)
    {
        /* @var $model Epicor_Customerconnect_Model_Erp_Mapping_Rmastatus */
        $model = $this->customerconnectErpMappingRmastatusFactory->create();
        $erp = $model->loadMappingByStore($erpCode, 'code', $storeId);

        return $erp;
    }

    /**
     * Gets the rma status description from its ERP code
     * 
     * @param string $code
     * @param string $description
     * 
     * @return string
     */
    public function getRmaStatusDescription($code, $description = '', $storeId = null)
    {
        $erp = $this->getRmaStatusMapping($code, $storeId);
        if ($erp->getStatus())
            $description = $erp->getStatus();
        if ($description == '')
            $description = $code;
        return $description;
    }

    /**
     * Return the ERP service call state / status for the given erp code.
     * 
     * @param string $erpCode
     * 
     * @return \Epicor\Customerconnect\Model\Erp\Mapping\Servicecallstatus
     */
    public function getServicecallStatusMapping($erpCode)
    {
        /* @var $model Epicor_Customerconnect_Model_Erp_Mapping_Servicecallstatus */
        $model = $this->customerconnectErpMappingServicecallstatusFactory->create();
        $erp = $model->loadMappingByStore($erpCode, 'code');

        return $erp;
    }

    /**
     * Gets the service call status description from its ERP code
     * 
     * @param string $code
     * @param string $description
     * 
     * @return string
     */
    public function getServicecallStatusDescription($code, $description = '')
    {
        $erp = $this->getServicecallStatusMapping($code);

        if ($erp->getStatus())
            $description = $erp->getStatus();
        if ($description == '')
            $description = $code;
        return $description;
    }

    /**
     * Return the reason code mapping for the given erp reason code
     * 
     * @param string $erpCode
     * 
     * @return \Epicor\Customerconnect\Model\Erp\Mapping\Reasoncode
     */
    public function getReasonCodeMapping($erpCode)
    {
        /* @var $model Epicor_Customerconnect_Model_Erp_Mapping_Reasoncode */
        $model = $this->customerconnectErpMappingReasoncodeFactory->create();
        $erp = $model->loadMappingByStore($erpCode, 'code');

        return $erp;
    }

    /**
     * Gets the reason code description from its ERP code
     * 
     * @param string $code
     * @param string $description
     * 
     * @return string
     */
    public function getReasonCodeDescription($code, $description = '')
    {
        $erp = $this->getReasonCodeMapping($code);

        if ($erp->getDescription()) {
            $description = $erp->getDescription();
        }

        if ($description == '') {
            $description = $code;
        }

        return $description;
    }
    
    public function getProductObject($sku, $is_configurator= false) {
        try{
            if($is_configurator){
                return $this->productRepository->get($sku, false, null, true);
            }else{
                return $this->productRepository->get($sku);
            }
        }catch(\Exception $e){
            return $this->dataObjectFactory->create();
        }
         
    }

    /**
     * Get the allowed Quote status that can be Confimed/Rejected
     *
     * @return array
     */
    public function getSelectQuoteStatuses()
    {
        $_allowedStatuses = $this->scopeConfig->getValue(self::XML_PATH_EPICOR_QUOTES_GENERAL_QUOTE_STATUS, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return (explode(',', $_allowedStatuses));
    }

    /**
     * Checks if the quote can be confirmed/rejected based on quote status
     * @param $quoteStatus
     * @param $rowData
     * @return bool
     */
    public function confirmRejectQuoteStatus($quoteStatus, $rowData)
    {
        $isDealer = $this->getCustomer()->isDealer();
        $module = $this->request->getModuleName();
        if ($isDealer && $module == 'dealerconnect') {
            if ($quoteStatus != \Epicor\Customerconnect\Model\Config\Source\Quotestatus::QUOTE_STATUS_AWAITING
                || $rowData->getQuoteEntered() != 'Y'
            ) {
                return true;
            }
        } else {
            $_allowedStatuses = $this->getSelectQuoteStatuses();
            if (!in_array($quoteStatus, $_allowedStatuses)
                || ($quoteStatus != \Epicor\Customerconnect\Model\Config\Source\Quotestatus::QUOTE_STATUS_PENDING
                    && $rowData->getQuoteEntered() != 'Y')
            ) {
                return true;
            }
        }
        return false;
    }

}
