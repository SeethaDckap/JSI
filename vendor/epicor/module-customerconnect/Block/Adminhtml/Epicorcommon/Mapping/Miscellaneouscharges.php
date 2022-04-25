<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Block\Adminhtml\Epicorcommon\Mapping;

class Miscellaneouscharges extends \Epicor\Common\Block\Adminhtml\Mapping\DefaultBlock\Grid {

    public function _construct() {
        $this->_controller = 'adminhtml\Epicorcommon_Mapping_Miscellaneouscharges';
        $this->_blockGroup = 'Epicor_Customerconnect';
        $this->_headerText = __('Miscellaneous Code Mapping');
        $this->_addButtonLabel = __('Add Mapping');
        parent::_construct();
    }

    /**
     * Create "New" button
     *
     * @return void
     */
    protected function _addNewButton() {
        $this->addButton(
            'add', [
                'label' => $this->getAddButtonLabel(),
                'onclick' => 'setLocation(\'' . $this->getCreateUrl() . '\')',
                'class' => 'add primary add-mapping'
            ]
        );
    }

}
