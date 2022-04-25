<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Block\Adminhtml\Sales\Returns;

class View extends \Magento\Backend\Block\Widget\Form\Container {

    public function __construct(
    \Magento\Backend\Block\Widget\Context $context, array $data = []
    ) {


        parent::__construct(
                $context, $data
        );

        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_sales_returns';
        $this->_blockGroup = 'epicor_comm';
        $this->_mode = 'view';

        $this->removeButton('delete');
        $this->removeButton('save');
        $this->removeButton('reset');
    }

    public function getHeaderText() {
        $header = __('Return');

        return $header;
    }

}
