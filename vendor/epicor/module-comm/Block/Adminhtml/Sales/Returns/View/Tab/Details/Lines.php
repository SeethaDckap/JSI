<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Block\Adminhtml\Sales\Returns\View\Tab\Details;

class Lines extends \Magento\Backend\Block\Widget\Grid\Container {

    public function __construct(
    \Magento\Backend\Block\Widget\Context $context, array $data = []
    ) {
        $this->_controller = 'adminhtml_sales_returns_view_tab_details_lines';
        $this->_blockGroup = 'Epicor_Comm';
        $this->_headerText = __('Lines');
        parent::__construct(
                $context, $data
        );

        $this->removeButton('add');
    }

}
