<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Message\Upload;


/**
 *
 * Response CRRC - RMA Return Reason Codes
 *
 * Process reason codes permitted in website and foreach erp account
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Crrc extends \Epicor\Comm\Model\Message\Upload
{

    private $_languageStores;
    private $_languageData;
    private $_request;

    /**
     * @var \Epicor\Customerconnect\Model\Erp\Mapping\ReasoncodeFactory
     */
    protected $customerconnectErpMappingReasoncodeFactory;

    /**
     * @var \Epicor\Customerconnect\Model\Erp\Mapping\Reasoncode\AccountsFactory
     */
    protected $customerconnectErpMappingReasoncodeAccountsFactory;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;
    public function __construct(
        \Epicor\Comm\Model\Context $context,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Epicor\Customerconnect\Model\Erp\Mapping\ReasoncodeFactory $customerconnectErpMappingReasoncodeFactory,
        \Epicor\Customerconnect\Model\Erp\Mapping\Reasoncode\AccountsFactory $customerconnectErpMappingReasoncodeAccountsFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [])
    {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->customerconnectErpMappingReasoncodeFactory = $customerconnectErpMappingReasoncodeFactory;
        $this->customerconnectErpMappingReasoncodeAccountsFactory = $customerconnectErpMappingReasoncodeAccountsFactory;

        parent::__construct($context, $resource, $resourceCollection, $data);
        $this->setConfigBase('epicor_comm_field_mapping/crrc_mapping/');
        $this->setMessageType('CRRC');
        $this->setLicenseType(array('Consumer', 'Customer'));
        $this->setMessageCategory(self::MESSAGE_TYPE_UPLOAD);
        $this->setStatusCode(self::STATUS_SUCCESS);
        $this->setStatusDescription('');

    }
    public function processAction()
    {  
        $this->_request = $this->getRequest();
        $this->erpData = $this->_request->getReasons();

        $this->_loadStores();
        $this->_loadStoreLanguages();
        $this->_processLanguageData();

        $this->_processReasonCodes();
        $this->_processReasonCodesAccounts();
    }

    private function _processReasonCodes()
    {
        $reasons = array();
        foreach ($this->_languageData as $storeId => $reasonsXml) {
            foreach ($reasonsXml as $reasonObject) {
                $reasonType = $this->getVarienData('reason_type', $reasonObject->getReason());
                $reasonDelete = $this->getVarienData('is_delete', $reasonObject->getReason());
                /* @var $model Epicor_Customerconnect_Model_Erp_Mapping_Reasoncode */
                $model = $this->customerconnectErpMappingReasoncodeFactory->create();

                $model->load($model->getIdByCodeAndStore($reasonObject->getReason()->getReasonCode(), $storeId));

                if ($reasonDelete == 'Y') {
                    $model->delete();
                } else {
                    $reasons[] = $reasonObject->getReason()->getReasonCode();
                    $model->setCode($reasonObject->getReason()->getReasonCode());
                    $model->setDescription($reasonObject->getLanguage()->getDescription());
                    $model->setStoreId($storeId);
                    $model->setType($reasonType ?: $model->getType());
                    $model->save();
                }
            }
        }

        $this->setMessageSubject(implode('', array_unique($reasons)));
    }

    private function _processReasonCodesAccounts()
    {
        $reasons = $this->getVarienDataArray('reason');
        foreach ($reasons as $reason) {
            $this->customerconnectErpMappingReasoncodeAccountsFactory->create()->deleteByCode($reason->getReasonCode());
            foreach ($this->getVarienDataArray('accounts', $reason) as $accountCode) {
                /* @var $model_account Epicor_Customerconnect_Model_Erp_Mapping_Reasoncode_Accounts */
                $model_account = $this->customerconnectErpMappingReasoncodeAccountsFactory->create();
                $model_account->setCode($reason->getReasonCode());

                $erpAccount = $this->_request->getBrand()->getCompany();
                $erpAccount .= $this->getHelper()->getUOMSeparator();
                $erpAccount .= $accountCode;
                $model_account->setErpAccount($erpAccount);
                $model_account->save();
            }
        }
    }

    /**
     * Processes the stores and sorts them into an array by language code
     */
    private function _loadStoreLanguages()
    {

        $this->_languageStores = array();
        $storeDefaultIncluded = false;

        foreach ($this->_stores as $store) {
            if (!$storeDefaultIncluded && $store->getId() == 0) {
                $storeDefaultIncluded = true;
            }
            $storeCode = $this->scopeConfig->getValue('general/locale/code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store->getId());

            // only add the language if we don't already have it
            if (!isset($this->_languageStores[$storeCode]) || !in_array($store->getId(), $this->_languageStores[$storeCode])) {
                $this->_languageStores[$storeCode][] = $store->getId();
            }
        }
        if (!$storeDefaultIncluded) {
            $storeCode = $this->scopeConfig->getValue('general/locale/code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, 0);
            $this->_languageStores[$storeCode][] = 0;
        }
    }

    /**
     * Processes the language group into an array of data
     */
    private function _processLanguageData()
    {
        $reasons = $this->getVarienDataArray('reason');

        if (empty($reasons)) {
            throw new \Exception('No reason codes provided', self::STATUS_GENERAL_ERROR);
        }

        foreach ($reasons as $reason) {
            $reasonHasDefaultLanguage = false;

            foreach ($this->getVarienDataArray('languages', $reason) as $reasonLanguage) {
                $helper = $this->getHelper();
                $language_codes = $helper->getLanguageMapping($reasonLanguage->getLanguageCode(), $helper::ERP_TO_MAGENTO);

                foreach ($language_codes as $language_code) {
                    if (isset($this->_languageStores[$language_code])) {
                        foreach ($this->_languageStores[$language_code] as $store_id) {
                            if (!$reasonHasDefaultLanguage && $store_id == 0) {
                                $reasonHasDefaultLanguage = true;
                            }
                            $obj= $this->dataObjectFactory->create();
                            $obj->addData(array('reason' => $reason, 'language' => $reasonLanguage));
                            $this->_languageData[$store_id][] = $obj;
                        }
                        $reasonLanguage->setLanguageCode($language_code);
                    }
                }
            }
            if (!$reasonHasDefaultLanguage) {
                
                $obj2= $this->dataObjectFactory->create();
                $obj2->addData(array('reason' => $reason, 'language' => $this->dataObjectFactory->create(array(
                        'description' => $reason->getReasonCode()))));             
                $this->_languageData[0][] = $obj2;
            }
        }

        if (empty($this->_languageData)) {
            throw new \Exception('Languages for the branding provided do not match any stores in the system', self::STATUS_GENERAL_ERROR);
        }
    }

}
