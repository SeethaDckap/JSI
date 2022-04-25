<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Customer\Dashboard;


/**
 * Dealer Invoices list
 */
class Invoices extends \Epicor\Common\Block\Customer\Dashboard
{

    const FRONTEND_RESOURCE = "Epicor_Customerconnect::customerconnect_account_invoices_read";

    protected $dashboardSection = 'dealer_dashboard_invoices';

    protected $sectionController = 'customer_dashboard_invoices';

    protected $sectionBlockGroup = 'Epicor_Dealerconnect';

    protected $sectionHeaderText = 'Recent Invoices';

    protected $viewAllUrlParam = 'customerconnect/invoices';
}
