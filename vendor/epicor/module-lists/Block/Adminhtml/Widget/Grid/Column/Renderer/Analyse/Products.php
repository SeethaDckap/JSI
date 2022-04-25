<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Block\Adminhtml\Widget\Grid\Column\Renderer\Analyse;


/**
 * Active column renderer
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Products extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->registry = $registry;
        parent::__construct(
            $context,
            $data
        );
    }


    /**
     * Render product grid column
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $isListExclude = $row->hasSetting('E');
        $listProducts = array_keys($row->getProducts());
        $accumulatedProducts = $this->registry->registry('epicor_lists_analyse_accumulated_products') ?: array();
        $accumulateType = $this->registry->registry('epicor_lists_analyse_accumulated_products_type');
        $sku = $this->getColumn()->getSku();

        if ($accumulateType == 'E') {
            $resultProducts = $isListExclude ? array_merge($accumulatedProducts, $listProducts) : array_diff($listProducts, $accumulatedProducts);
            $accumulateType = $isListExclude ? 'E' : 'I';
        } elseif ($accumulateType == 'I') {
            $resultProducts = $isListExclude ? array_diff($accumulatedProducts, $listProducts) : array_intersect($accumulatedProducts, $listProducts);
        } else {
            $resultProducts = $listProducts;
            $accumulateType = $isListExclude ? 'E' : 'I';
        }
        $this->registry->unregister('epicor_lists_analyse_accumulated_products');
        $this->registry->unregister('epicor_lists_analyse_accumulated_products_type');
        $this->registry->register('epicor_lists_analyse_accumulated_products', $resultProducts);
        $this->registry->register('epicor_lists_analyse_accumulated_products_type', $accumulateType);

        if ($sku) {
            if ((in_array($sku, $resultProducts) && $accumulateType == 'I') || (!in_array($sku, $resultProducts) && $accumulateType == 'E')) {
                $html = __('Available');
            } else {
                $html = __('Not Available');
            }
        } else {
            $html = "Filtered List Total: " . count($resultProducts);
        }

        $listCount = count($listProducts);

        $html .= " <br/>  Total Products on List: " . $listCount;
        $html .= '<input type="hidden" class="filteredrow" value=\'' . count($resultProducts) . '\' />';
        $data = array(
            'list_id' => $row->getId(),
            'products' => $resultProducts,
            'type' => $accumulateType
        );

        $html .= '<input type="hidden" name="data" class="data" value=\'' . base64_encode(json_encode($data)) . '\' />';

        return $html;
    }

}
