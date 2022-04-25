<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Customer\Quotes\Listing;


/**
 * Customer Dealer Quotes list Grid config
 * 
 * Note: columns for this grid are configured in the Magento Admin: Configuration > Dealer Connect
 * 
 * @category   Epicor
 * @package    Epicor_Dealerconnect
 * @author     Epicor Websales Team
 */
class Grid extends \Epicor\Customerconnect\Block\Customer\Rfqs\Listing\Grid
{

    const FRONTEND_RESOURCE_CONFIRMREJECT = 'Dealer_Connect::dealer_quotes_confirmrejects';

    const FRONTEND_RESOURCE_DETAIL = 'Dealer_Connect::dealer_quotes_details';

    const FRONTEND_RESOURCE_EXORT = 'Dealer_Connect::dealer_quotes_export';

    public function setCollection($collection)
    {
        $dealerFilter = [
            'dealer' => 'Y',
        ];
        $collection->setRowFilters($dealerFilter);
        $this->_collection = $collection;
    }

}
