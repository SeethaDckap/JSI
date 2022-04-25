<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Block\Claims\Details;

use \Epicor\Common\Model\Xmlvarien;

class ConfiguredInfo extends \Epicor\Common\Block\Customer\Info
{
    private $customerConnectMessagingHelper;
    private $scopeConfig;
    private $registry;
    /**
     * @var \Epicor\Comm\Model\Erp\Mapping\Claimstatus
     */
    protected $_claimStatusMapping;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Epicor\Customerconnect\Helper\Data $helper,
        \Epicor\Customerconnect\Helper\Messaging $customerConnectMessagingHelper,
        \Epicor\Comm\Model\Erp\Mapping\Claimstatus $_claimStatusMapping,
        array $data = []
    ) {
        $this->customerConnectMessagingHelper = $customerConnectMessagingHelper;
        $this->scopeConfig = $context->getScopeConfig();
        $this->registry = $registry;
        $this->_claimStatusMapping = $_claimStatusMapping;
        parent::__construct(
            $context,
            $helper,
            $data
        );
    }

    public function _construct()
    {
        $this->setColumnCount(1);
        $this->setTemplate('Epicor_Dealerconnect::claims/details/configured-info.phtml');
        $invoiceMsg = $this->registry->registry('dealer_connect_claim_details');
        if ($invoiceMsg) {
            $this->_infoData = $this->getGridDetails($invoiceMsg);
        }
        $this->setTitle(__('Claim Information'));
        parent::_construct();
    }

    public function getIdIdentifier($index)
    {
        return $this->getIdValueFromMap($index);
    }

    public function isViewableValue($key)
    {
        if ($this->isNewClaimView()) {
            $viewable = ['serialNumbers > serialNumber', 'identification_number'];
            if (!in_array($key, $viewable)) {
                return false;
            }
        }

        return true;
    }

    private function isNewClaimView()
    {
        return preg_match('/dealerconnect\/claims\/new/', $this->getCurrentUrl()) === 1;
    }

    private function getCurrentUrl()
    {
        return $this->_storeManager->getStore()->getCurrentUrl();
    }

    public function getClaimLocationNumber()
    {
        $claim = $this->registry->registry('dealer_connect_claim_details');
        return $claim->getLocationNumber();
    }

    private function getIdValueFromMap($key)
    {
        $map = [
            'serialNumbers > serialNumber' => 'serial_num',
            'identification_number' => 'identity_num',
            'product_code' => 'prod_code'
        ];

        if (array_key_exists($key, $map)) {
            return $map[$key];
        }
    }


    /**
     * @param $path
     * @return mixed
     */
    public function getConfig($path)
    {
        return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getGridDetails($dataMainObject)
    {
        $dataObject = $dataMainObject;
        $getConfig = $this->getConfig('dealerconnect_enabled_messages/DCLD_request/grid_informationconfig');
        $configData = unserialize($getConfig);

        if (!$configData) {
            return [];
        }
        $indexValues = [];


        foreach ($configData as $key => $data) {
            if (isset($data['index'])) {
                $inputIndex = $data['index'];
                if ($this->isChildConfigValuesSet($inputIndex)) {
                    $index = $inputIndex;
                } else {
                    $index = $this->decamelize($inputIndex);
                }
                $value = $dataObject->getData($index);
                if ($this->check_your_datetime($value)) {
                    $value = $this->renderDate($dataObject->getData($index));
                }

                switch (true) {
                    case ($index == 'claim_status' && $dataObject->getData($index)):
                        $value = $this->_claimStatusMapping
                            ->getCollection()->addFieldToFilter('erp_code',$dataObject->getData($index))
                            ->getFirstItem()
                            ->getData('claim_status');
                        if($value) {
                            $value = ucfirst($value);
                        }else{
                            $value = $dataObject->getData($index);
                        }
                        break;
                }

                if ($this->isChildConfigValuesSet($index)) {
                    $value = $this->getChildConfigValues($index, $dataObject);
                }
                $indexValues[$inputIndex] = [
                    'index' => $index,
                    'header' => $data['header'],
                    'value' => $value
                ];
            }
        }
        return $indexValues;
    }

    public function getCaseNumber()
    {
        if($caseNumber = $this->_infoData['case_number']['value'] ?? ''){
            return $caseNumber;
        }

        $claimsDetails = $this->registry->registry('dealer_connect_claim_details');
        return $claimsDetails->getData('case_number');
    }

    public function getProductCode()
    {
        if ($inventoryInfo = $this->getInventoryInfo()) {
            return $inventoryInfo->getProductCode();
        }
    }

    private function getInventoryInfo()
    {
        return $this->registry->registry('dealer_connect_claim_inventory');
    }
}