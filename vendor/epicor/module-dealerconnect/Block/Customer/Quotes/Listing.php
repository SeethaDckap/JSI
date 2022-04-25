<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Block\Customer\Quotes;


/**
 * Customer Dealer Quotes list
 *
 * @category   Epicor
 * @package    Epicor_Dealer
 * @author     Epicor Websales Team
 */
class Listing extends \Epicor\Customerconnect\Block\Customer\Rfqs\Listing
{

    const FRONTEND_RESOURCE = 'Dealer_Connect::dealer_quotes_read';

    const FRONTEND_RESOURCE_CREATE = 'Dealer_Connect::dealer_quotes_create';

    const ACCESS_MESSAGE_DISPLAY = TRUE;

    protected function _setupGrid()
    {
        $this->_controller = 'customer_quotes_listing';
        $this->_blockGroup = 'Epicor_Dealerconnect';
        $this->_headerText = __('Quotes');

        $msgHelper = $this->commMessagingHelper;
        $enabled = $msgHelper->isMessageEnabled('customerconnect', 'crqu');

        $erpAccount = $msgHelper->getErpAccountInfo();
        $currencyCode = $erpAccount->getCurrencyCode($this->storeManager->getStore()->getBaseCurrencyCode());
        if ($enabled && $this->_isAccessAllowed(static::FRONTEND_RESOURCE_CREATE) && $currencyCode) {
            $url = $this->getUrl('*/*/new/');
            $this->addButton(
                'new', array(
                'label' => __('New Quote'),
                'onclick' => 'setLocation(\'' . $url . '\')',
                'class' => 'add',
            ), 10
            );
        }
    }

}
