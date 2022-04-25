<?php
/**
 * Copyright © 2010-2019 Epicor Software. All rights reserved.
 */

namespace Epicor\Comm\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Customer\Model\Session as customerSession;

class B2BMasquerade implements SectionSourceInterface
{
    /**
     * @var
     */
    private $actions;

    /**
     * @var
     */
    private $_locationHelper;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $_urlInterface;

    /**
     * @var null
     */
    private $childAccounts = null;

    /**
     * @var null
     */
    private $erpAccount = null;

    /**
     * @var null
     */
    private $masqueradeAccount = null;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    private $commHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * B2BMasquerade constructor.
     *
     * @param \Epicor\Comm\Helper\Locations   $commLocationsHelper
     * @param \Magento\Framework\UrlInterface $urlInterface
     * @param \Epicor\Comm\Helper\Data        $commHelper
     * @param customerSession                 $customerSession
     */
    public function __construct(
        \Epicor\Comm\Helper\Locations $commLocationsHelper,
        \Magento\Framework\UrlInterface $urlInterface,
        \Epicor\Comm\Helper\Data $commHelper,
        customerSession $customerSession
    ) {
        $this->commLocationsHelper = $commLocationsHelper;
        $this->_urlInterface = $urlInterface;
        $this->commHelper = $commHelper;
        $this->scopeConfig = $commHelper->getScopeConfig();
        $this->customerSession = $customerSession;
    }

    /**
     * Section Data.
     *
     * @return array
     */
    public function getSectionData()
    {
        return [
            'isAllow'              => $this->isAllowed(),
            'isMasquerading'       => $this->isMasquerading(),
            'showCartOptions'      => $this->showCartOptions(),
            'cartAction'           => $this->getCartActionsToArray(),
            'childAccounts'        => $this->getChildAccounts(),
            'masqueradAccountName' => $this->getMasqueradeAccount()->getname(),
            'actualAccountName'    => $this->getActualAccount()->getname(),
        ];
    }

    /**
     * Is Allowed.
     *
     * @return bool
     */
    public function isAllowed()
    {
        $customerSession = $this->customerSession;
        /* @var $customerSession \Magento\Customer\Model\Session */
        $customer = $customerSession->getCustomer();
        /* @var $customer \Epicor\Dealerconnect\Model\Customer */

        /* @var $erpAccount \Epicor\Dealerconnect\Model\Customer\Erpaccount */
        $erpAccount = $this->getActualAccount();

        $allowed = false;
        if ( ! $customer->isSalesRep() && $customer->isMasqueradeAllowed()
            && ! $erpAccount->isDefaultForStore()
        ) {
            $children = $this->getChildAccounts();
            if (count($children) > 0) {
                $allowed = true;
            }
        }

        return $allowed;
    }

    /**
     * Is Location Selected.
     *
     * @return array
     */
    public function isLocationSelected()
    {
        return $this->getLocationHelper()->getCustomerDisplayLocationCodes();
    }

    /**
     * Get Location Helper
     *
     * @return \Epicor\Comm\Helper\Locations
     */
    public function getLocationHelper()
    {
        if ( ! $this->_locationHelper) {
            $this->_locationHelper = $this->commLocationsHelper;
        }

        return $this->_locationHelper;
    }

    /**
     * Get session customer allowed locations
     *
     * @return array
     */
    public function getCustomerAllowedLocations()
    {
        $locations = $this->getLocationHelper()->getCustomerAllowedLocations();

        if ( ! is_array($locations)) {
            $locations = array();
        }

        return $locations;
    }

    /**
     * Return Url.
     *
     * @return string
     */
    public function getReturnUrl()
    {
        $url = $this->_urlInterface->getCurrentUrl();

        return $this->commHelper->getUrlEncoder()->encode($url);
    }

    /**
     * Get list of locations
     *
     * @return array
     */
    protected function getItems()
    {
        $items = [];
        $locations = $this->getCustomerAllowedLocations();
        foreach ($locations as $item) {
            $items[] = [
                'code' => $item->getCode(),
                'name' => $item->getName(),
            ];
        }

        return $items;
    }

    /**
     * Is Masquerading.
     *
     * @return bool
     */
    public function isMasquerading()
    {
        return $this->commHelper->isMasquerading();
    }

    /**
     * Show Cart Options.
     *
     * @return bool
     */
    public function showCartOptions()
    {
        $actions = $this->getCartActions();

        return count($actions) > 0;
    }

    /**
     * Cart Actions.
     *
     * @return array
     */
    public function getCartActions()
    {
        if (is_null($this->actions)) {
            $this->actions = array();
            $customer = $this->customerSession->getCustomer();
            if ($customer->isMasqueradeAllowed()) {
                if ($customer->isMasqueradeCartClearAllowed()) {
                    $this->actions['clear'] = __('Clear');
                }

                if ($customer->isMasqueradeCartRepriceAllowed()) {
                    $this->actions['reprice'] = __('Reprice');
                }
            }

            if (empty($this->actions)) {
                $action
                    = $this->scopeConfig->getValue('epicor_comm_erp_accounts/masquerade/default_cart_action',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $this->actions[$action] = ($action == 'clear') ? __('Clear')
                    : __('Reprice');
            }
        }

        return $this->actions;
    }

    /**
     * Cart Actions To Array
     *
     * @return array
     */
    protected function getCartActionsToArray()
    {
        $items = [];
        $cartAction = $this->getCartActions();
        foreach ($cartAction as $val => $label) {
            $items[] = [
                'value' => $val,
                'label' => $label,
            ];
        }

        return $items;
    }

    /**
     * Child Accounts.
     *
     * @return array
     */
    public function getChildAccounts()
    {
        if (is_null($this->childAccounts)) {
            $this->childAccounts = array();

            /* @var $erpAccount \Epicor\Comm\Model\Customer\Erpaccount */
            $erpAccount = $this->getActualAccount();
            $this->childAccounts = $erpAccount->getChildAccounts('', true);
        }

        $items = [];
        if (count($this->childAccounts) > 0) {
            foreach ($this->childAccounts as $value) {
                $items[] = [
                    'value' => $value->getId(),
                    'label' => $value->getName(),
                ];
            }
        }

        return $items;
    }

    /**
     * Actual Account.
     *
     * @return \Epicor\Comm\Model\Customer\Erpaccount|false|null
     */
    public function getActualAccount()
    {
        if (is_null($this->erpAccount)) {
            $this->erpAccount = $this->commHelper->getErpAccountInfo(null,
                'customer', null, false);
        }

        return $this->erpAccount;
    }

    /**
     * Masquerade Account.
     *
     * @return \Epicor\Comm\Model\Customer\Erpaccount|false|null
     */
    public function getMasqueradeAccount()
    {
        if (is_null($this->masqueradeAccount)) {
            $this->masqueradeAccount = $this->commHelper->getErpAccountInfo();
        }

        return $this->masqueradeAccount;
    }
}