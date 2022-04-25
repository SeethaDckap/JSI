<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Customer\Parts\Details;


/**
 * Parts info data setup
 * 
 * @category   Epicor
 * @package    Epicor_Supplierconnect
 * @author     Epicor Websales Team
 */
class Info extends \Epicor\Supplierconnect\Block\Customer\Info
{
    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;

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
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        array $data = []
    ) {
        $this->commMessagingHelper = $commMessagingHelper;
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
        $partMsg = $this->registry->registry('supplier_connect_part_details');
        if ($partMsg) {
            $part = $partMsg->getPart();
            if ($part) {
                $this->_infoData = $this->getGridDetails($part);
            }
        }
        $this->setTitle(__('Part Information'));
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
        $currency = $this->commMessagingHelper->getCurrencyMapping($dataObject->getCurrencyCode(), \Epicor\Comm\Helper\Messaging::ERP_TO_MAGENTO);
        $getConfig = $this->getConfig('supplierconnect_enabled_messages/SPLD_request/grid_informationconfig');
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
                    case (strpos($index, 'price')):
                        $value = $this->commMessagingHelper->formatPrice($dataObject->getData($index), true, $currency);
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
