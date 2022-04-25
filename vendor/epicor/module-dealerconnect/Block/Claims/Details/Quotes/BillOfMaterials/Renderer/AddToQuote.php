<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Claims\Details\Quotes\BillOfMaterials\Renderer;


/**
 * Column Renderer for Add to Quote 
 *
 * @category   Epicor
 * @package    Epicor_SalesRep
 * @author     Epicor Websales Team
 */
class AddToQuote extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    public function render(\Magento\Framework\DataObject $row)
    {
        $html = '';
        $sku = $row['new']['product_code']?:$row['product_code'];
        $uom = $row['unit_of_measure_code'];
        $params = array('sku'=>$sku,'uom'=>$uom);
        $block = $this->getLayout()->createBlock('Epicor\Dealerconnect\Block\Claims\Details\Quotes\BillOfMaterials\AddToQuote');
        $html .= $block->setData($params)->toHtml();
        return $html;
    }

}
