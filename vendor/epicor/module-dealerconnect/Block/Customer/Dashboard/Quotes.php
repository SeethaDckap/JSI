<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Customer\Dashboard;


/**
 * Dealer Quotes list
 */
class Quotes extends \Epicor\Common\Block\Customer\Dashboard
{

    const FRONTEND_RESOURCE = "Dealer_Connect::dealer_quotes_read";

    protected $dashboardSection = 'dealer_dashboard_quotes';

    protected $sectionController = 'customer_dashboard_quotes';

    protected $sectionBlockGroup = 'Epicor_Dealerconnect';

    protected $sectionHeaderText = 'Recent Quotes';

    protected $viewAllUrlParam = 'quotes';

}
