<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Customer\Dashboard;


/**
 * Dealer Orders list
 */
class Shipments extends \Epicor\Common\Block\Customer\Dashboard
{

    const FRONTEND_RESOURCE = "Epicor_Customerconnect::customerconnect_account_shipments_read";

    protected $dashboardSection = 'dealer_dashboard_shipments';

    protected $sectionController = 'customer_dashboard_shipments';

    protected $sectionBlockGroup = 'Epicor_Dealerconnect';

    protected $sectionHeaderText = 'Recent Shipments';

    protected $viewAllUrlParam = 'customerconnect/shipments';

}
