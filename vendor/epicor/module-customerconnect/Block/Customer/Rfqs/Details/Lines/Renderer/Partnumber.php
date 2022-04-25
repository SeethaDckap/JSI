<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Rfqs\Details\Lines\Renderer;


/**
 * RFQ Part Number comments renderer
 * 
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Partnumber extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var \Epicor\Customerconnect\Helper\Rfq
     */
    protected $customerconnectRfqHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    
    /**
     * @var \Epicor\Customerconnect\Helper\Messaging
     */
    protected $customerconnectMessagingHelper;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Epicor\Customerconnect\Helper\Rfq $customerconnectRfqHelper,
        \Magento\Framework\Registry $registry,
        \Epicor\Customerconnect\Helper\Messaging $customerconnectMessagingHelper,
        array $data = []
    ) {
        $this->customerconnectRfqHelper = $customerconnectRfqHelper;
        $this->registry = $registry;
        $this->customerconnectMessagingHelper = $customerconnectMessagingHelper;
        parent::__construct(
            $context,
            $data
        );
    }


    public function render(\Magento\Framework\DataObject $row)
    {
        $helper = $this->customerconnectRfqHelper;

        $index = $this->getColumn()->getIndex();
        $productCode = $this->escapeHtml($row->getData($index));
        $product = $this->customerconnectMessagingHelper->getProductObject((string) $row->getData('product_code'));
        $productCode = $helper->getAttributeValueFromLineByDescription($row, array('ewaSku', 'ewa_sku', 'ewa sku')) ?: $productCode;
        $html = $productCode;
        if ($this->registry->registry('rfqs_editable')) {
            if (($row->getGroupSequence() || $row->getEwaCode()) && $product->getEccConfigurator()) {
                $html .= '<br /><a href="javascript: lines.configureEwaProduct(\'' . $row->getUniqueId() . '\')">';
                $html .= __('Edit Configuration');
                $html .= '</a>';
            }
        }

        return '<span class="product_code_display">' . $html . '</span>';
    }

}
