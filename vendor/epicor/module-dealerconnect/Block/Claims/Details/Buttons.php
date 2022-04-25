<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Block\Claims\Details;


/**
 * Dealer Claim Details Quotes page buttons
 *
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Buttons extends \Epicor\Customerconnect\Block\Customer\Rfqs\Details\Buttons
{

    const FRONTEND_RESOURCE_CREATE = "Dealer_Connect::dealer_claim_create";
    const FRONTEND_RESOURCE_EDIT = 'Dealer_Connect::dealer_claim_edit';
    const FRONTEND_RESOURCE_CONFIRMREJECT = 'Dealer_Connect::dealer_claim_confirmrejects';

    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('claims/details/buttons.phtml');
    }

    public function getDuplicateUrl()
    {
        $parms = $this->getRequest()->getParams();
        if (array_key_exists('rfq_serialize_data', $parms)) {          // if duplicate_url is specified, quote will not be and vice versa
            $duplicateUrl = substr($parms['rfq_serialize_data'], strpos($parms['rfq_serialize_data'], "=") + 1);
            $realUrl = current(explode('quote_address', $duplicateUrl));
            return urldecode($realUrl);
        } else {
            $params = array(
                'quote' => $this->getRequest()->getParam('quote')
            );
            return $this->getUrl('dealerconnect/claims/quoteduplicate/', $params);
        }
    }

    public function getConfirmUrl()
    {
        return $this->getUrl('dealerconnect/claims/quoteconfirm');
    }

    public function getRejectUrl()
    {
        return $this->getUrl('dealerconnect/claims/quotereject');
    }

    public function showDuplicate()
    {
        if ($this->registry->registry('hide_all_buttons')
            || $this->canEditClaim() == 0
        ) {
            return false;
        }

        $show = $this->_isAccessAllowed(static::FRONTEND_RESOURCE_EDIT);

        if ($show) {
            $helper = $this->commMessagingHelper;
            $show = $helper->isMessageEnabled('customerconnect', 'crqu');

            $erpAccount = $helper->getErpAccountInfo();
            $currencyCode = $erpAccount->getCurrencyCode($this->storeManager->getStore()->getBaseCurrencyCode());

            if ($show && !$currencyCode) {
                $show = false;
            }
        }
        $action = $this->getRequest()->getActionName();
        if ($action == 'new' || $action == 'duplicate') {
            $show = false;
        }elseif ($action == 'quotedetails' &&
            $this->_isAccessAllowed(static::FRONTEND_RESOURCE_EDIT)){
            $show = true;
        }

        return $show;
    }

    public function showConfirm()
    {
        if ($this->canEditClaim() == 0) {
            return false;
        }
        return parent::showConfirm();
    }

    public function showReject()
    {
        if ($this->canEditClaim() == 0) {
            return false;
        }
        return parent::showConfirm();
    }

    /**
     * @return mixed
     */
    protected function canEditClaim()
    {
        return $this->getRequest()->getParam('can_editclaim');
    }
}