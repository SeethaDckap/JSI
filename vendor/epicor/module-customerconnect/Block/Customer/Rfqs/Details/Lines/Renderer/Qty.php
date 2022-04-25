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
class Qty extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $catalogProductFactory;
    
    /**
     * @var \Epicor\Common\Helper\Data
     */
    protected $commonHelper;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Registry $registry,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->commonHelper = $commonHelper;
        $this->catalogProductFactory = $catalogProductFactory;
        parent::__construct(
            $context,
            $data
        );
    }


    public function render(\Magento\Framework\DataObject $row)
    {
        $key = $this->registry->registry('rfq_new') ? 'new' : 'existing';
        $index = $this->getColumn()->getIndex();
        $productCode = $row->getData('product_code');
        $product = $this->catalogProductFactory->create();
        $product->load($product->getIdBySku($productCode));
        $decimalPlaces = $this->commonHelper->getDecimalPlaces($product);
        $validation = "";
        if ($decimalPlaces !== '') {
            $validation = 'data-validate="{\'validatedecimalplace\':'. $decimalPlaces . '}"';
        }
        if ($row->getData($index)) {
            $value = $row->getData($index) * 1;
        } else {
            $value = $row->getData($index);
        }
        if ($this->registry->registry('rfqs_editable')) {
            $html = '<input '.$validation.' type="text" name="lines[' . $key . '][' . $row->getUniqueId() . '][' . $index . ']" value="' . $value . '" class="lines_' . $index . '"/>';
        } else {
            $html = $value;
            $html .= '<input type="hidden" name="lines[' . $key . '][' . $row->getUniqueId() . '][' . $index . ']" value="' . $value . '" class="lines_' . $index . '"/>';
        }

        return $html;
    }

}
