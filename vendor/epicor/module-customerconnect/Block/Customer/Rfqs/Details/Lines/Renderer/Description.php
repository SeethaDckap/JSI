<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Rfqs\Details\Lines\Renderer;


/**
 * RFQ line editable text field renderer
 * 
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Description extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var \Epicor\Comm\Helper\Configurator
     */
    protected $commConfiguratorHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    
    /**
     * @var \Epicor\Customerconnect\Helper\Messaging
     */
    protected $customerconnectMessagingHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Epicor\Comm\Helper\Configurator $commConfiguratorHelper,
        \Magento\Framework\Registry $registry,
        \Epicor\Customerconnect\Helper\Messaging $customerconnectMessagingHelper,
        array $data = []
    ) {
        $this->commConfiguratorHelper = $commConfiguratorHelper;
        $this->registry = $registry;
        $this->customerconnectMessagingHelper = $customerconnectMessagingHelper;
        $this->scopeConfig = $context->getScopeConfig();
        parent::__construct(
            $context,
            $data
        );
    }


    public function render(\Magento\Framework\DataObject $row)
    {
        $helper = $this->commConfiguratorHelper;

        $key = $this->registry->registry('rfq_new') ? 'new' : 'existing';
        $index = $this->getColumn()->getIndex();

        $type = $row->getData('product_code__attributes_type');
        $sku = (string) $row->getData('product_code');
        $product = $this->customerconnectMessagingHelper->getProductObject($sku);
        $description = array();
        $value = $row->getData($index) ? $row->getData($index) : '';
        if ($this->registry->registry('rfqs_editable') && $type != 'S') {
            $html = '<input type="text" name="lines[' . $key . '][' . $row->getUniqueId() . '][' . $index . ']" value="' . $value . '" class="lines_' . $index . ' required-entry"/>';
        } else {
            if ($product && $product->getEccConfigurator()) {
                if ($helper->getEwaDisplay('base_description')) {
                    $description[] = $row->getDescription();
                }
            } else {
                $description[] = $row->getData($index);
            }
            $html = '<input type="hidden" name="lines[' . $key . '][' . $row->getUniqueId() . '][' . $index . ']" value="' . $value . '" class="lines_' . $index . '"/>';
        }

        if ($row->getAttributes()) {
            $attGroup = $row->getAttributes();
            $attributes = $attGroup->getasarrayAttribute();

            // This gets the quote sort order from admin and reorders the ewa fields accordingly
            $attributeData = array();
            $ewaAttributes = array('ewaTitle' => 'ewa_title', 'ewaSku' => 'ewa_sku', 'ewaShortDescription' => 'ewa_short_description', 'ewaDescription' => 'ewa_description');
            foreach ($attributes as $attribute) {
                $attributeData[$attribute['description']] = $attribute['value'];
            }
            $newOptionsOrder = $this->scopeConfig->getValue('Epicor_Comm/ewa_options/quote_display_fields', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $newoptionsOrder = $newOptionsOrder ? unserialize($newOptionsOrder) : null;

            $newOptionByTypeOrder = array();

            foreach ($newoptionsOrder as $key => $option) {
                //               $newOptionByTypeOrder[] = array($key=>$option) ;
                $newOptionByTypeOrder[$option['ewaquotesortorder']] = array($key => $option);
            }
            $requiredEwaOptions = array_intersect_key($newOptionByTypeOrder, array_flip($ewaAttributes));
            $ewaAttributes = array_flip(array_replace($requiredEwaOptions, array_flip($ewaAttributes)));
            /* @var $product Epicor_Comm_Model_Product */
            foreach ($ewaAttributes as $key => $ewaAttribute) {
                if (isset($attributeData[$key])) {
                    if ($this->registry->registry('rfqs_editable')) {
                        $setBase64 = ($key =="ewaSku") ? $attributeData[$key] :base64_encode($attributeData[$key]);
                        $row->setData($ewaAttribute, $setBase64);
                        $product->setData($ewaAttribute, $setBase64);
                    }
                    if ($helper->getEwaDisplay($ewaAttribute)) {
                        $description[] = $attributeData[$key];
                    }
                }
            }
        }
        return '<span class="description_display">' . $html . join('<br /><br />', $description) . '</span>';
    }

}
