<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Customer\Returns\Lines\Renderer;


class Qty extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Comm\Helper\Returns
     */
    protected $commReturnsHelper;
    
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $catalogProductFactory;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Registry $registry,
        \Epicor\Comm\Helper\Returns $commReturnsHelper,
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->commReturnsHelper = $commReturnsHelper;
        $this->catalogProductFactory = $catalogProductFactory;
        parent::__construct(
            $context,
            $data
        );
    }


    public function render(\Magento\Framework\DataObject $row)
    {
        $productCode = $row->getProductCode();
        /* @var $row Epicor_Comm_Model_Customer_ReturnModel_Line */
        $product = $this->catalogProductFactory->create();
        $product->load($product->getIdBySku($productCode));
        $decimalPlaces = $this->commReturnsHelper->getDecimalPlaces($product);
        $qtyReturned = $this->commReturnsHelper->qtyRounding($row->getQtyReturned(), $decimalPlaces);
        $qtyOrdered = $this->commReturnsHelper->qtyRounding($row->getQtyOrdered(), $decimalPlaces);
        $validation = "";
        if ($decimalPlaces !== '') {
            $validation = 'data-validate="{\'validatedecimalplace\':'. $decimalPlaces . '}"';
        }
        if (!$this->registry->registry('review_display') && $row->isActionAllowed('Quantity')) {
            $disabled = $row->getToBeDeleted() == 'Y' ? ' disabled="disabled"' : '';
            $html = '<input ' . $validation . ' type="text" name="lines[' . $row->getUniqueId() . '][quantity_returned]" value="' . $qtyReturned . '" class="return_line_quantity_returned"' . $disabled . '/>';
        } else {
            $html = $qtyReturned;
        }

        $helper = $this->commReturnsHelper;
        /* @var $helper Epicor_Comm_Helper_Returns */

        $source = $row->getSourceType();

        $html .= '<span class="return_line_quantity_ordered">';
        if ($source != 'sku') {
            $html .= ' / ';
            $html .= $qtyOrdered;
        }
        $html .= '</span>';

        return $html;
    }

}
