<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Customer\Rfqs\Details;


class Generalinfo extends \Epicor\Supplierconnect\Block\Customer\Info
{
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
        $rfq = $this->registry->registry('supplier_connect_rfq_details');
        $this->_infoData = $this->getGridDetails($rfq);

        $this->setTitle(__('General Information'));
        $this->setOnLeft(true);
        $this->setColumnCount(1);
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
        $getConfig = $this->getConfig('supplierconnect_enabled_messages/SURD_request/grid_informationconfig');
        $configData = unserialize($getConfig);

        if (!$configData) {
            $oldData = [];
        }
        $indexVals = [];

        if($dataObject){

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
        }
        return $indexVals;
    }

    public function _toHtml()
    {
        $rfq = $this->registry->registry('supplier_connect_rfq_details');
        $html = '';

        $helper = $this->supplierconnectHelper;
        /* @var $helper Epicor\Supplierconnect\Helper\Data */
        $rfq = base64_encode(serialize($helper->varienToArray($rfq)));
        $html = '<input type="hidden" name="old_data" value="' . $rfq . '" />';

        $html .= parent::_toHtml();
        return $html;
    }

}
