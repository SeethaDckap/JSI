<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Renderer\Erpimages;


/**
 * ERP Image type list renderer
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Types extends \Magento\Backend\Block\AbstractBlock
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
        $typeNames = array();

        $types =$row->getTypes();

        if (!empty($types)) {
            $types = $types->getData();

            if (!empty($types)) {
                foreach ($types as $type) {
                    $typeNames[] = @$this->_types[$type];
                }
            }
        }

        $html .= implode(',', $typeNames);

        return $html;
    }

}
