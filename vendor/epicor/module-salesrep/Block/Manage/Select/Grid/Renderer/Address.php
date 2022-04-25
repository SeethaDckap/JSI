<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Block\Manage\Select\Grid\Renderer;


/**
 * Column Renderer for Sales ERP Account Select Grid Address
 *
 * @category   Epicor
 * @package    Epicor_SalesRep
 * @author     Epicor Websales Team
 */
class Address extends \Epicor\Common\Block\Adminhtml\Widget\Grid\Column\Renderer\Action
{

    public function renderAddress(\Magento\Framework\DataObject $row, $type)
    {
        $addressFields = array('name', 'address1', 'address2', 'address3', 'city', 'county', 'country', 'postcode');
        $glue = '';
        $text = '';
        foreach ($addressFields as $field) {
            $fieldData = trim($row->getData($type . '_' . $field));
            if ($fieldData && !empty($fieldData)) {
                $text .= $glue . $fieldData;
                $glue = ', ';
            }
        }

        return $text;
    }

}
