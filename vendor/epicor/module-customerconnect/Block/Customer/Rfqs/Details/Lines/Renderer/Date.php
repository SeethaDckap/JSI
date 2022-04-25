<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Rfqs\Details\Lines\Renderer;


/**
 * RFQ Line row date renderer
 *
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Date extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $customerconnectHelper;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;

    /**
     * @var \Epicor\Customerconnect\Helper\Messaging
     */
    protected $customerconnectMessagingHelper;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Registry $registry,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Epicor\Customerconnect\Helper\Messaging $customerconnectMessagingHelper,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->customerconnectHelper = $customerconnectHelper;
        $this->_localeResolver = $localeResolver;
        $this->customerconnectMessagingHelper = $customerconnectMessagingHelper;
        parent::__construct(
            $context,
            $data
        );
    }


    public function render(\Magento\Framework\DataObject $row)
    {
        $key = $this->registry->registry('rfq_new') ? 'new' : 'existing';
        $helper = $this->customerconnectHelper;

        $index = $this->getColumn()->getIndex();
        $date = $row->getData($index);
        $data = '';

        if (!empty($date)) {
            try {
                $Format = $this->_localeDate->getDateFormatWithLongYear();
                $getFormat = $helper->convertPhpToIsoFormat($Format);
                $data = date($getFormat, strtotime($row->getData($index)));
            } catch (\Exception $ex) {
                $data = $row->getData($index);
            }
        }

        if ($this->registry->registry('rfqs_editable')) {
            $sku = (string) $row->getData('product_code');
            $product = $this->customerconnectMessagingHelper->getProductObject($sku);
            $product->setData($index, $data);
            //M1 > M2 Translation Begin (Rule p2-6.4)
            //$format = Mage::app()->getLocale()->getDateStrFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
            $format = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);
            //M1 > M2 Translation End
            $html = '<input id="line_' . $row->getUniqueId() . '_request_date" name="lines[' . $key . '][' . $row->getUniqueId() . '][request_date]" type="text" value="' . $data . '"  class="lines_request_date"/>
                     <script type="text/javascript">// <![CDATA[
                        require([
                            "jquery",
                            "mage/calendar"
                        ], function(jQuery) {
                          var dateFormats="";
                          if(jQuery("#date_input_format").length) {
                             var dateFormats = jQuery("#date_input_format").val();
                          }
                          jQuery(\'#line_' . $row->getUniqueId() . '_request_date\').calendar({dateFormat: dateFormats,showOn:"both"});
                        })
                        // ]]></script>';
        } else {
            $html = $data;
            $html .= '<input id="line_' . $row->getUniqueId() . '_request_date" name="lines[' . $key . '][' . $row->getUniqueId() . '][request_date]" type="hidden" value="' . $data . '"  class="lines_request_date"/>';
        }

        return $html;
    }

}