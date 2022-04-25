<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Block\Customer\Orders\Details;


class Info extends \Epicor\Customerconnect\Block\Customer\Info
{

    const FRONTEND_RESOURCE_INFORMATION_READ = 'Epicor_Customerconnect::customerconnect_account_orders_details';
    protected $dateFormates = array('order_date', 'required_date');
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $salesOrderFactory;

    /**
     * @var \Epicor\Lists\Helper\Frontend\Contract
     */
    private $contract;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Magento\Framework\Registry $registry,
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Sales\Model\OrderFactory $salesOrderFactory,
        \Epicor\Lists\Helper\Frontend\Contract $contract,
        array $data = []
    )
    {
        $this->registry = $registry;
        $this->scopeConfig = $context->getScopeConfig();
        $this->commHelper = $commHelper;
        $this->salesOrderFactory = $salesOrderFactory;
        $this->contract = $contract;
        parent::__construct(
            $context,
            $customerconnectHelper,
            $data
        );
    }

    public function _construct()
    {
        parent::_construct();
        $order = $this->registry->registry('customer_connect_order_details');
        $this->setTemplate('Epicor_Common::details/info.phtml');
        if ($order) {
            $this->_infoData = $this->getGridDetails($order);

            $this->setTitle(__('Order Information'));
        }
    }

    public function getGridDetails($dataObject)
    {
        $shipStatus = $this->commHelper->isShipStatus();
        $eccAdditionalreference = $this->commHelper->isEccAdditionalReference();
        $customer= $this->commHelper->getCustomer();
        $allowTaxExemptRef =$this->commHelper->isTaxExemptionAllowed($customer->getErpaccountId());

        $getConfig = $this->getConfig('customerconnect_enabled_messages/CUOD_request/grid_informationconfig');
        $configData = unserialize($getConfig);

        if (!$configData) {
            $oldData = [];
        }
        $indexVals = [];


        foreach ($configData as $key => $data) {
            if (isset($data['index'])) {
                if ($this->isChildConfigValuesSet($data['index'])) {
                    $index = $data['index'];
                } else {
                    $index = $this->decamelize($data['index']);
                }
                $value = $dataObject->getData($index);
                if ($this->check_your_datetime($value)) {
                    $value = $this->renderDate($dataObject->getData($index));
                }
                if ($this->isChildConfigValuesSet($index)) {
                    $value = $this->getChildConfigValues($index, $dataObject);
                }

                $indexVals[$data['index']] = [
                    'index' => $index,
                    'header' => $data['header'],
                    'value' => $value
                ];

                //remove contract index if contracts are not enabled
                if ($index == 'contract_code' && (!$this->scopeConfig->isSetFlag('epicor_lists/global/enabled',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
                    || $this->contract->contractsDisabled())) {
                    unset($indexVals[$index]);
                }
                if ($index == 'additional_reference' && !$eccAdditionalreference) {
                    unset($indexVals[$index]);
                }
                if ($index == 'ship_status' && !$shipStatus) {
                    unset($indexVals[$index]);
                }
                if ($index == 'tax_exempt_reference' && !$allowTaxExemptRef) {
                    unset($indexVals[$index]);
                }

            }
        }
        return $indexVals;
    }


}
