<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Renderer\Erpimages;


class Types  extends \Magento\Backend\Block\AbstractBlock
{

    private $_types = array(
        'L' => 'Large',
        'T' => 'Thumbnail',
        'S' => 'Small',
        'G' => 'Gallery'
    );

    public function _generateHtml(\Magento\Framework\DataObject $row)
    { 
        $html = '';

        $types = array();

        $value = $this->getValue();

        if (!empty($value)) {
            $value = $value->getData();

            if (!empty($value)) {
                foreach ($value as $type) {
                    $types[] = $this->_types[$type];
                }
            }
        }

        $html .= implode(',', $types);

        return $html;
    }

}
