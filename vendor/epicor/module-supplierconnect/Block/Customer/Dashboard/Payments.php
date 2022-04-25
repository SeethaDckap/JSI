<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Customer\Dashboard;


/**
 * Customer Orders list
 */
class Payments extends \Epicor\Common\Block\Generic\Listing
{
    const FRONTEND_RESOURCE = 'Epicor_Supplier::supplier_payments_read';

    protected function _setupGrid()
    {
        $this->_controller = 'customer_dashboard_payments';
        $this->_blockGroup = 'Epicor_Supplierconnect';
        $this->_headerText = __('Recent Payments');
    }

    protected function _postSetup()
    {
        $this->setBoxed(true);
        parent::_postSetup();
    }

    public function getHeaderHtml()
    {
        $html = parent::getHeaderHtml();
        $html .= '<a class="view_all" href="' . $this->getUrl('*/payments/') . '">' . __('View All') . '</a>';

        return $html;
    }
    
}
