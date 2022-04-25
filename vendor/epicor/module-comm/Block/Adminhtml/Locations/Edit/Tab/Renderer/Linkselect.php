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
class Linkselect extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    private $_typesMap = array(
        \Epicor\Comm\Model\Location\Link::LINK_TYPE_EXCLUDE => 'Included',
        \Epicor\Comm\Model\Location\Link::LINK_TYPE_INCLUDE => 'Excluded',
        'N' => 'No Restriction',
    );
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
        /* @var $row Epicor_Comm_Model_Customer_Erpaccount */
        $value = $row->getLinkType() ?: 'N';

        $html = '<select name="' . $this->getColumn()->getFormFieldName() . '[' . $row->getEntityId() . ']">';

        foreach ($this->_typesMap as $code => $label) {
            $selected = ($code == $value) ? ' selected="selected"' : '';
            $html .= '<option value="' . $code . '"' . $selected . '>' . __($label) . '</option>';
        }

        $html .= '</select>';

        return $html;
    }

}
