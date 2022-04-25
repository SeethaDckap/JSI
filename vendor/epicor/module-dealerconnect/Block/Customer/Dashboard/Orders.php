<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Customer\Dashboard;


/**
 * Dealer Orders list
 */
class Orders extends \Epicor\Common\Block\Customer\Dashboard
{

    const FRONTEND_RESOURCE = "Dealer_Connect::dealer_orders_read";

    protected $dashboardSection = 'dealer_dashboard_orders';

    protected $sectionController = 'customer_dashboard_orders';

    protected $sectionBlockGroup = 'Epicor_Dealerconnect';

    protected $sectionHeaderText = 'Recent Orders';

    protected $viewAllUrlParam = 'orders';

}
