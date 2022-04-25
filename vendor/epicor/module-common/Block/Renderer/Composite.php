<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Renderer;


/**
 * Part number display on invoice details
 *
 * @author Gareth.James
 */
class Composite extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    public function render(\Magento\Framework\DataObject $row)
    {

        $keys = $this->getColumn()->getKeys();
        $labels = $this->getColumn()->getLabels();

        $html = '';
        $join = '';
        foreach ($keys as $key) {
            $html .= $join;
            $html .= isset($labels[$key]) ? '<strong>' . $labels[$key] . ': </strong>' : '';
            $html .= is_numeric($row->getData($key)) ? floatval($row->getData($key)) : $row->getData($key);   // remove traling 0s from a numeric field
            $join = $this->getColumn()->getJoin();
        }

        return $html;
    }

}
