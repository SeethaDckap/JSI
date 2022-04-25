<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Block\Adminhtml\Sales;

/**
 * Description of Return
 *
 * @author Paul.Ketelle
 */
class Returns extends \Magento\Backend\Block\Widget\Grid\Container {

    public function __construct(
    \Magento\Backend\Block\Widget\Context $context, array $data = []
    ) {
        $this->_controller = 'adminhtml\Sales_Returns';
        $this->_blockGroup = 'Epicor_Comm';
        $this->_headerText = __('Returns');
        parent::__construct(
                $context, $data
        );

        $this->removeButton('add');
    }

}
