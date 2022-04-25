<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Helper;


/**
 * Helper for List Session
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Session extends \Epicor\Lists\Helper\Data
{

    private $knownKeys = array(
        'ecc_contract_selection_started',
        'ecc_selected_contract_shipto',
        'ecc_selected_list',
        'ecc_qop_selected_list',
        'ecc_selected_contract',
        'ecc_contracts_mandatory',
        'ecc_multiple_contracts',
        'ecc_quickorderpad_list',
        'ecc_non_contract_allowed',
        'ecc_contract_checkout_disabled',
        'ecc_optional_select_contract_show',
        'ecc_contract_select_filter',
        'ecc_shipto_select_filter',
        'ecc_contract_line_item_filter',
        'ecc_contract_store',
        'ecc_contract_cart_codes',
        'ecc_contract_cart_address_codes',
        'ecc_contract_line_checked_existing',
        'ecc_arpayments_quote'
    );

    /**
     * Checks to see if the store has changed, and if so, wipe the session values
     * 
     * @return $this
     */
    public function storeCheck()
    {
        $sessionStore = $this->getSession()->getData('ecc_contract_store');
        $currentStore = $this->storeManager->getStore()->getId();
        if (is_null($sessionStore)) {
            $this->getSession()->setData('ecc_contract_store', $currentStore);
        } else if ($sessionStore != $currentStore) {
            $this->clear();
            $quote = $this->checkoutCart->getQuote();
            /* @var $quote Epicor_Comm_Model_Quote */
            if ($quote->hasItems()) {
                $transportObject = $this->dataObjectFactory->create();
                $transportObject->setContractId(-1);
                $this->_eventManager->dispatch('epicor_lists_contract_select_before', array('transport' => $transportObject));
                $this->_eventManager->dispatch('epicor_lists_contract_select_after', array('transport' => $transportObject));
            }
            $this->getSession()->setData('ecc_contract_store', $currentStore);
        }

        return $this;
    }

    /**
     * Sets a value in the session
     *
     * @param string $key
     * @param mixed $value
     *
     * @return \Epicor_Lists_Helper_Session
     */
    public function setValue($key, $value)
    {
        $this->getSession()->setData($key, $value);
        return $this;
    }

    /**
     * Gets a value from the session
     *
     * @param string $key
     * 
     * @return mixed
     */
    public function getValue($key)
    {
        return $this->getSession()->getData($key);
    }

    /**
     * Gets a value from the session
     *
     * @param string $key
     *
     * @return mixed
     */
    public function clear()
    {
        foreach ($this->knownKeys as $key) {
            $this->getSession()->unsetData($key);
        }
    }

    /**
     * Retrieve customer session model object
     *
     * @return \Magento\Customer\Model\Session
     */
    protected function getSession()
    {
        //M1 > M2 Translation Begin (Rule p2-5.1)
        //return Mage::getSingleton('customer/session');
        return $this->customerSessionFactory->create();
        //M1 > M2 Translation End
    }

}
