<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Block\Customer\Invoices\Details;

use Epicor\Customerconnect\Block\Customer\Invoices\Listing\Renderer\InvoiceType;
use Epicor\Customerconnect\Block\Listing\Renderer\LinkorderFactory as LinkOrderFactory;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Epicor\Customerconnect\Helper\Data as CustomerConnectHelper;
use Epicor\Customerconnect\Block\Customer\Invoices\Listing\Renderer\CentrallyCollected as CcRenderer;

class ConfiguredInfo extends \Epicor\Common\Block\Customer\Info
{
    const ORDER_DATA_INDEX = 'our_order_number';
    const CENTRAL_COLLECTION_INDEX = 'central_collection';

    private $scopeConfig;
    private $registry;
    private $dataObjectFactory;
    private $linkOrderFactory;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var CcRenderer
     */
    private $ccRenderer;
    /**
     * @var \Epicor\Lists\Helper\Frontend\Contract
     */
    private $contract;

    public function __construct(
        DataObjectFactory $dataObjectFactory,
        LinkOrderFactory $linkOrderFactory,
        Registry $registry,
        Context $context,
        CustomerConnectHelper $customerConnectHelper,
        \Epicor\Comm\Helper\Data $commHelper,
        CcRenderer $ccRenderer,
        \Epicor\Lists\Helper\Frontend\Contract $contract,
        array $data = []
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        $this->registry = $registry;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->linkOrderFactory = $linkOrderFactory;
        $this->commHelper = $commHelper;
        $this->ccRenderer = $ccRenderer;
        $this->contract = $contract;
        parent::__construct($context, $customerConnectHelper, $data);
    }

    public function _construct()
    {
        parent::_construct();
        $this->setColumnCount(3);
        $this->setTemplate('Epicor_Customerconnect::customerconnect/configured-info.phtml');
        $this->setTitle(__('Invoice Information'));
        $invoices = $this->registry->registry('customer_connect_invoices_details');
        if ($invoices) {
            $this->_infoData = $this->getGridDetails($invoices);
        }

        //remove contract index if contracts are not enabled
        if (!$this->scopeConfig->isSetFlag('epicor_lists/global/enabled',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
                || $this->contract->contractsDisabled()) {
            unset($this->_infoData['contract_code']);
        }

    }

    public function getGridDetails($dataMainObject)
    {
        $dataObject = $dataMainObject;
        $getConfig = $this->getConfig('customerconnect_enabled_messages/CUID_request/grid_informationconfig');
        $configData = unserialize($getConfig);
        $customer= $this->commHelper->getCustomer();
        $allowTaxExemptRef =$this->commHelper->isTaxExemptionAllowed($customer->getErpaccountId());

        if (!$configData) {
            return [];
        }
        $indexValues = [];

        foreach ($configData as $key => $data) {
            if (isset($data['index'])) {
                $inputIndex = $data['index'];

                if ($this->isChildConfigValuesSet($inputIndex)) {
                    $index = $inputIndex;
                    $value = $this->getChildConfigValues($index, $dataObject);
                } else {
                    if($inputIndex == 'tax_exempt_reference' && $allowTaxExemptRef){
                        $index = $this->decamelize($inputIndex);
                    }else{
                        $index = $this->decamelize($inputIndex);
                    }
                    $value = $this->getDefaultConfigTypeValue($index, $dataObject);
                }

                if ($inputIndex === '_attributes_type') {
                    $attributes = $dataObject->getData('_attributes');
                    if ($attributes) {
                        $value = $dataObject->getData('_attributes')->getType();
                        $value = InvoiceType::MAPPED_VALUE_TYPES[$value];
                    }
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

    private function getDefaultConfigTypeValue($index, $dataObject)
    {
        if($index === self::ORDER_DATA_INDEX){
            $value = $this->renderOrderLink($dataObject);
        }else if($index === self::CENTRAL_COLLECTION_INDEX){
            $value = $this->renderCc($dataObject);
        }else{
            $value = $dataObject->getData($index);
        }

        if ($this->check_your_datetime($value)) {
            $value = $this->renderDate($dataObject->getData($index));
        }

        return $value;
    }

    private function renderOrderLink($dataObject)
    {
        $renderer = $this->linkOrderFactory->create();
        $columnData = $this->dataObjectFactory->create();
        $columnData->setIndex(self::ORDER_DATA_INDEX);
        $renderer->setColumn($columnData);
        return $renderer->render($dataObject);
    }

    /**
     *
     * Renders Centrally Collected Info
     * @param \Magento\Framework\DataObject $dataObject
     * @return string
     */
    private function renderCc($dataObject)
    {
        $renderer = $this->ccRenderer;
        $columnData = $this->dataObjectFactory->create();
        $columnData->setIndex(self::CENTRAL_COLLECTION_INDEX);
        $renderer->setColumn($columnData);
        return $renderer->render($dataObject);
    }
}
