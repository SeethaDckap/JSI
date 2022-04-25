<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Common\Block\Adminhtml\Mapping;

class Erporderstatus extends \Epicor\Common\Block\Adminhtml\Mapping\DefaultBlock\Grid {

    public function __construct(
    \Magento\Backend\Block\Widget\Context $context, array $data = []
    ) {
        $this->_controller = 'adminhtml_mapping_erporderstatus';
        $this->_blockGroup = 'Epicor_Common';
        $this->_headerText = __('ERP Order Status Mapping');
        $this->_addButtonLabel = __('Add Mapping');
        parent::__construct(
                $context, $data
        );
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
