<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Supplierconnect\Block\Customer\Orders\Details;


class Info extends \Epicor\Supplierconnect\Block\Customer\Info
{


    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    protected $dateFormates = array('order_date', 'due_date', 'promise_date', 'post_date');

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Epicor\Supplierconnect\Helper\Data $supplierconnectHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        array $data = []
    )
    {
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

        $orderMsg = $this->registry->registry('supplier_connect_order_details');

        if ($orderMsg) {

            $order = $orderMsg->getPurchaseOrder();

            if ($order) {
                $this->_infoData = $this->getGridDetails($order);
            }
        }

        $this->setTitle(__('Order Information'));
    }


    /**
     * @param $path
     * @return mixed
     */
    public function getConfig($path)
    {
        return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getGridDetails($dataObject)
    {
        $getConfig = $this->getConfig('supplierconnect_enabled_messages/SPOD_request/grid_informationconfig');
        $oldData = unserialize($getConfig);
        if (!$oldData) {
            $oldData = [];
        }
        $indexVals = array();



        foreach ($oldData as $key => $oldValues) {
            if (isset($oldValues['index'])) {
                if (strpos($oldValues['index'], '>') !== false) {
                    $index =$oldValues['index'];
                } else {
                    $index =$this->decamelize($oldValues['index']);
                }
                $value = $dataObject->getData($index);
                if (in_array($index, $this->dateFormates)) {
                    $value = $this->renderDate($dataObject->getData($index));
                }
                if ($index == 'order_status') {
                    $value = $this->getHelper()->getErpOrderStatusDescription($dataObject->getOrderStatus());
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

                if ($index == 'order_confirmed') {
                    $value = ($dataObject->getOrderConfirmed() != '') ?
                        ($dataObject->getOrderConfirmed() == 'C') ?
                            'Confirmed' :
                            'Rejected' :
                        null;
                }
                $indexVals[$oldValues['index']] = array(
                    'index' => $index, 'header' => $oldValues['header'], 'value' => $value
                );
            }
        }

        return $indexVals;
    }

    public function _toHtml()
    {
        $rfq = $this->registry->registry('supplier_connect_order_details');
        $html = '';
        $helper = $this->supplierconnectHelper;
        $arr = $helper->varienToArray($rfq);
        if (array_key_exists('lines', $arr)) {
            $lines = $arr['lines'];
            if (count($lines) === 1) {
                unset($arr['lines']['line']['product']);
            } else {
                foreach ($lines as $line) {
                    foreach ($line as $key => $l) {
                        unset($arr['lines']['line'][$key]['product']);
                    }
                }
            }
        }
        $rfq = base64_encode(serialize($arr));
        $html = '<input type="hidden" name="oldData" value="' . $rfq . '" />';

        $html .= parent::_toHtml();
        return $html;
    }

}