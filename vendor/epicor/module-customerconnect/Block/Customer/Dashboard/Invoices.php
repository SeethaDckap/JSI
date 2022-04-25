<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Dashboard;


/**
 * Customer Orders list
 */
class Invoices extends \Epicor\Common\Block\Generic\Listing
{

    const FRONTEND_RESOURCE = 'Epicor_Customerconnect::customerconnect_account_invoices_read';

    protected function _setupGrid()
    {
        $this->_controller = 'customer_dashboard_invoices';
        $this->_blockGroup = 'Epicor_Customerconnect';
        $this->_headerText = __('Recent Invoices');
    }

    protected function _postSetup()
    {
        $this->setBoxed(true);
        parent::_postSetup();
    }

    public function getHeaderHtml()
    {
        $html = parent::getHeaderHtml();
        $html .= '<a class="view_all" href="' . $this->getUrl('*/invoices/') . '">' . __('View All') . '</a>';

        return $html;
    }
    
}
