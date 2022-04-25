<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Rfqs\Details;


use Epicor\Comm\Model\Customer\ReturnModel\NewFileAttachments;

/**
 * RFQ editable options display
 *
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Options extends \Magento\Framework\View\Element\Template
{

    private $_methods;
    private $_method;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $customerconnectHelper;

    /**
     * @var \Epicor\Customerconnect\Helper\Rfq
     */
    protected $customerconnectRfqHelper;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var \Magento\Framework\Locale\FormatInterface
     */
    protected $localeFormat;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var NewFileAttachments
     */
    private $newFileAttachments;

    public function __construct(
        NewFileAttachments $newFileAttachments,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Epicor\Customerconnect\Helper\Rfq $customerconnectRfqHelper,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        array $data = []
    )
    {
        $this->registry = $registry;
        $this->customerconnectHelper = $customerconnectHelper;
        $this->customerconnectRfqHelper = $customerconnectRfqHelper;
        $this->_localeResolver = $localeResolver;
        $this->jsonHelper = $jsonHelper;
        $this->localeFormat = $localeFormat;
        $this->scopeConfig = $context->getScopeConfig();
        parent::__construct(
            $context,
            $data
        );
        $this->newFileAttachments = $newFileAttachments;
    }


    public function _construct()
    {
        parent::_construct();

        $this->setTemplate('Epicor_Customerconnect::customerconnect/customer/account/rfqs/details/options.phtml');
        $this->setTitle(__('Options'));
    }

    public function _toHtml()
    {
        $rfq = $this->registry->registry('customer_connect_rfq_details');
        $html = '';
        //$rfq = base64_encode(serialize($arr));
        //$html = '<input type="hidden" name="old_data" value="' . $rfq . '" />';
        if ($this->registry->registry('rfq_duplicate')) {
            $html .= '<input type="hidden" name="is_duplicate" value="1" />';
        }
        $html .= parent::_toHtml();
        return $html;
    }

    public function getWebReference()
    {
        $rfq = $this->registry->registry('customer_connect_rfq_details');
        if ($rfq) {
            if ($rfq->getWebReference()) {
                $webRef = $rfq->getWebReference();
            } else {
                $rfqHelper = $this->customerconnectRfqHelper;

                $webRef = $rfqHelper->getNextRfqWebRef();

                // Set Prefix for web reference
                $webReferencePrefix = $this->scopeConfig->getValue('epicor_quotes/general/prefix', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                if ($webReferencePrefix && substr($webRef, 0, strlen($webReferencePrefix)) !== $webReferencePrefix) {
                    $webReferencePrefix .= $webRef;
                    return $webReferencePrefix;
                }
            }
        }
        return $webRef;
    }

    public function getWebReferencePrefix()
    {
        return $this->scopeConfig->getValue('epicor_quotes/general/prefix', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getRequiredDate()
    {
        $rfq = $this->registry->registry('customer_connect_rfq_details');
        $data = '';
        if ($rfq) {
            $helper = $this->customerconnectHelper;

            $date = $rfq->getRequiredDate();

            if (!empty($date)) {
                try {
                    $Format = $this->_localeDate->getDateFormatWithLongYear();
                    $getFormat = $helper->convertPhpToIsoFormat($Format);
                    $data = date($getFormat, strtotime($rfq->getRequiredDate()));
                } catch (\Exception $ex) {
                    $data = $date;
                }
            }
        }
        return $data;
    }

    public function getMappedShippingMethod()
    {
        $rfq = $this->registry->registry('customer_connect_rfq_details');
        if (!$this->_method && $rfq) {
            $helper = $this->customerconnectHelper;

            $this->_method = $helper->getShippingMethodMapping(
                $rfq->getDeliveryMethod(), \Epicor\Comm\Helper\Messaging::ERP_TO_MAGENTO, false
            );
        }

        return $this->_method;
    }

    public function getShippingMethodsData()
    {
        if (!$this->_methods) {
            $helper = $this->customerconnectHelper;

            $carriers = $helper->getShippingmethodList(true);

            $carriers['other'] = array(
                'value' => array(
                    'other_other' => array(
                        'value' => 'other',
                        'label' => __('Please Specify Below'),
                    )
                ),
                'label' => __('Other')
            );

            $this->_methods = $carriers;
        }

        return $this->_methods;
    }

    public function getRfqShippingMethodValue()
    {
        $carriers = $this->getShippingMethodsData();
        $mappedValue = $this->getMappedShippingMethod();

        $isOther = true;

        foreach ($carriers as $carrier) {
            foreach ($carrier['value'] as $method) {
                if ($method['value'] == $mappedValue) {
                    $isOther = false;
                }
            }
        }

        if ($mappedValue && $isOther) {
            $mappedValue = 'other';
        }

        return $mappedValue;
    }

    public function getRfqShippingMethodLabel()
    {
        $carriers = $this->getShippingMethodsData();
        $mapped = $this->getMappedShippingMethod();
        $label = $mapped;

        foreach ($this->getShippingMethodsData() as $carrier) {
            foreach ($carrier['value'] as $method) {
                if ($method['value'] == $mapped) {
                    $label = $carrier['label'] . ' - ' . $method['label'];
                }
            }
        }

        if ($label == $mapped) {
            $label = __('Other') . ' - ' . $label;
        }

        return $label;
    }

    public function getJsonConfig()
    {
        $config = array(
            //M1 > M2 Translation Begin (Rule p2-6.4)
            //'priceFormat' => Mage::app()->getLocale()->getJsPriceFormat(),
            'priceFormat' => $this->localeFormat->getPriceFormat(),
            //M1 > M2 Translation End
        );

        //M1 > M2 Translation Begin (Rule p2-7)
        //return Mage::helper('core')->jsonEncode($config);
        return $this->jsonHelper->jsonEncode($config);
        //M1 > M2 Translation End
    }

    //M1 > M2 Translation Begin (Rule p2-6.4)
    public function getResolver()
    {
        return $this->_localeDate;
    }
    //M1 > M2 Translation End

    //M1 > M2 Translation Begin (Rule p2-8)
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

    /**
     * @return \Epicor\Customerconnect\Helper\Data
     */
    public function getCustomerconnectHelper()
    {
        return $this->customerconnectHelper;
    }
    //M1 > M2 Translation End

    public function getMaxAttachmentFileSize()
    {
        return $this->newFileAttachments->getMaxFileNameLength();
    }

}
