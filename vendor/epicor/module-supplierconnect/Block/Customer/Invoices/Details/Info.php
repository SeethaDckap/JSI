<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Customer\Invoices\Details;


class Info extends \Epicor\Supplierconnect\Block\Customer\Info
{

    /**
     * @var \Epicor\Customerconnect\Helper\Messaging
     */
    protected $customerconnectMessagingHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Epicor\Supplierconnect\Helper\Data $supplierconnectHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Epicor\Customerconnect\Helper\Messaging $customerconnectMessagingHelper,

        array $data = []
    ) {
        $this->customerconnectMessagingHelper = $customerconnectMessagingHelper;
        $this->scopeConfig = $context->getScopeConfig();
        parent::__construct(
            $context,
            $supplierconnectHelper,
            $registry,
            $localeResolver,
            $backendHelper,
            $urlEncoder,
            $data
        );
    }
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('supplierconnect/customer/account/infonew.phtml');
        $invoiceMsg = $this->registry->registry('supplier_connect_invoice_details');
        if ($invoiceMsg) {
            $this->_infoData = $this->getGridDetails($invoiceMsg);
        }
        $this->setTitle(__('Invoice Information'));
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
        $dataObject = $dataMainObject->getInvoice();
        $getConfig = $this->getConfig('supplierconnect_enabled_messages/SUID_request/grid_informationconfig');
        $configData = unserialize($getConfig);

        if (!$configData) {
            $oldData = [];
        }
        $indexVals = [];


        foreach ($configData as $key => $data) {
            if (isset($data['index'])) {
                if (strpos($data['index'], '>') !== false) {
                    $index = $data['index'];
                } else {
                    $index =$this->decamelize($data['index']);
                }
                $value = $dataObject->getData($index);
                if ($this->check_your_datetime($value)) {
                    $value = $this->renderDate($dataObject->getData($index));
                }

                switch (true) {
                    case ($index == 'invoice_status' && $dataObject->getData($index)):
                        $value = $this->customerconnectMessagingHelper->getInvoiceStatusDescription($dataObject->getData($index));
                        break;
                    case ($index == 'invoice_number' && $dataMainObject->getData($index)):
                        $value = $dataMainObject->getData($index);
                        break;
                }

                if (strpos($index, '>') !== false) {
                    $getUserDefined =  explode( ">", $index);
                    $decamelize = $this->decamelize($getUserDefined[0]);
                    $decamelizeValues = $this->decamelize($getUserDefined[1]);
                    if(count($getUserDefined) =="3") {
                        $decamelizeValues1 = $this->decamelize($getUserDefined[2]);
                        $value = (isset($dataObject->getData($decamelize)[$decamelizeValues][$decamelizeValues1]))? $dataObject->getData($decamelize)[$decamelizeValues][$decamelizeValues1]: '';
                    } elseif(count($getUserDefined) =="4") {
                        $decamelizeValues1 = $this->decamelize($getUserDefined[2]);
                        $decamelizeValues2 = $this->decamelize($getUserDefined[3]);
                        $value = (isset($dataObject->getData($decamelize)[$decamelizeValues][$decamelizeValues1][$decamelizeValues2]))? $dataObject->getData($decamelize)[$decamelizeValues][$decamelizeValues1][$decamelizeValues2]: '';
                    } else {
                        $value = (isset($dataObject->getData($decamelize)[$decamelizeValues]))? $dataObject->getData($decamelize)[$decamelizeValues]: '';
                    }
                    if ($this->check_your_datetime($value)) {
                        $value = $this->renderDate($value);
                    }
                }
                $indexVals[$data['index']] = [
                    'index' => $index,
                    'header' => $data['header'],
                    'value' => $value
                ];
            }
        }
        return $indexVals;
    }

}
