<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Common\Block\Adminhtml\Mapping;

class Reasoncode extends \Epicor\Common\Block\Adminhtml\Mapping\DefaultBlock\Grid {

    public function _construct() {
        $this->_controller = 'adminhtml\Mapping_Reasoncode';
        $this->_blockGroup = 'Epicor_Common';
        $this->_headerText = __('Reason Code Mapping');
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
