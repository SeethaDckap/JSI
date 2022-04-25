<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Locations\Edit\Tab\Renderer;


/**
 * Entity register log details renderer
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author Epicor Websales Team
 */
class Productenabler extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    public function __construct(
        \Magento\Backend\Block\Context $context,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $data
        );
    }


    /**
     * Render column
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $selected = $row->getLocationCode() !== null ? 'checked="checked"' : '';

        $html = '<input name="' . $this->getColumn()->getName() . '[' . $row->getEntityId() . ']" type="checkbox" ' . $selected . ' onclick="return productLocations.rowEdit(this,' . $row->getEntityId() . ');return false;" />';

        return $html;
    }

}
