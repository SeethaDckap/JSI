<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Rfqs\Details;


/**
 * RFQ details - non-editable info block
 * 
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Info extends \Epicor\Common\Block\Customer\Info
{

    const FRONTEND_RESOURCE_INFORMATION_READ = \Epicor\AccessRight\Acl\RootResource::FRONTEND_RESOURCE;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Customerconnect\Helper\Messaging
     */
    protected $customerconnectMessagingHelper;

    protected $_infoData =[];

    protected $_dateFormat = \IntlDateFormatter::SHORT;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Epicor\Customerconnect\Helper\Data $helper,
        \Epicor\Customerconnect\Helper\Messaging $customerconnectMessagingHelper,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->customerconnectMessagingHelper = $customerconnectMessagingHelper;
        parent::__construct(
            $context,
            $helper,
            $data
        );
        $rfq = $this->registry->registry('customer_connect_rfq_details');
        if($rfq){
            $this->_infoData = $this->getGridDetails($rfq);
        }
    }


    public function _construct()
    {
        parent::_construct();

        $this->setTemplate('Epicor_Customerconnect::customerconnect/customer/account/rfqs/details/info.phtml');
        $this->setTitle(__('Information'));
    }

    public function _toHtml()
    {
        $rfq = $this->registry->registry('customer_connect_rfq_details');
        $html = '';
        if ($rfq) {
            $helper = $this->helper;
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
            $html = '<input type="hidden" name="old_data" value="' . $rfq . '" />';
        }
        $html .= parent::_toHtml();
        return $html;
    }

    public function getQuoteStatus()
    {
        $rfq = $this->registry->registry('customer_connect_rfq_details');

        $helper = $this->customerconnectMessagingHelper;
        return ($rfq) ? $helper->getErpquoteStatusDescription($rfq->getQuoteStatus()) : '';
    }

    /**
     * @param $key
     * @return mixed
     */
    public function registry($key)
    {
        return $this->registry->registry($key);
    }

    /**
     * @param $key
     * @param $value
     * @param bool $graceful
     */
    public function register($key, $value, $graceful = false)
    {
        $this->registry->register($key, $value, $graceful);
    }

    /**
     * @param $key
     */
    public function unregister($key)
    {
        $this->registry->unregister($key);
    }

    public function getGridDetails($dataObject)
    {
        $getConfig = $this->getConfig('customerconnect_enabled_messages/CRQD_request/grid_informationconfig');
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

                if ($data['index'] == 'quote_status') {
                    $value = $this->getQuoteStatus();
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
