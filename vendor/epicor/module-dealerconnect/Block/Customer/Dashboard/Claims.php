<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Customer\Dashboard;


/**
 * Dealer Claims list
 */
class Claims extends \Epicor\Common\Block\Customer\Dashboard
{

    const FRONTEND_RESOURCE = "Dealer_Connect::dealer_claim_read";

    protected $dashboardSection = 'dealer_dashboard_claims';

    protected $sectionController = 'customer_dashboard_claims';

    protected $sectionBlockGroup = 'Epicor_Dealerconnect';

    protected $sectionHeaderText = 'Recent Claims';

    protected $viewAllUrlParam = 'claims';
}
