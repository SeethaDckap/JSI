<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Block\Contract\Selection;


/**
 * Quick add block
 * 
 * Displays the quick add to Cart / wishlist block
 * 
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Sideblock extends \Magento\Framework\View\Element\Template
{

    private $_contracts;
    /* @var $_helper Epicor_Lists_Helper_Frontend_Contract */
    private $_helper;

    /**
     * @var \Epicor\Lists\Helper\Frontend\Contract
     */
    protected $listsFrontendContractHelper;

    /**
     * @var \Magento\Framework\Url\Helper\Data
     */
    protected $frameworkHelperDataHelper;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $checkoutCart;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Epicor\Lists\Helper\Frontend\Contract $listsFrontendContractHelper,
        \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Checkout\Model\Cart $checkoutCart,
        array $data = []
    ) {
        $this->listsFrontendContractHelper = $listsFrontendContractHelper;
        $this->frameworkHelperDataHelper = $frameworkHelperDataHelper;
        $this->commHelper = $commHelper;
        $this->checkoutCart = $checkoutCart;
        parent::__construct(
            $context,
            $data
        );
    }


    public function _construct()
    {
        parent::_construct();
        $this->setTitle(__('Contract Selector'));
        $this->_helper = $this->listsFrontendContractHelper;
    }

    public function getContracts()
    {
        if (!$this->_contracts) {
            $contracts = $this->_helper->getActiveContracts();
            $sortedContracts = array();
            foreach ($contracts as $contract) {
                $sortedContracts[$contract->getTitle() . $contract->getId()] = $contract;
            }

            ksort($sortedContracts);
            $this->_contracts = $sortedContracts;
        }

        return $this->_contracts;
    }

    public function getSessionContract()
    {
        $contractHelper = $this->listsFrontendContractHelper;
        /* @var $contractHelper Epicor_Lists_Helper_Frontend_Contract */

        return $contractHelper->getSelectedContract();
    }

    public function getReturnUrl()
    {
        $url = $this->frameworkHelperDataHelper->getCurrentUrl();
        return $this->commHelper->urlEncode($url);
    }

    /**
     * Returns whether the sidebar block can be shown
     * 
     * @return boolean
     */
    public function showSideBarBlock()
    {
        $contractHelper = $this->listsFrontendContractHelper;
        /* @var $contractHelper Epicor_Lists_Helper_Frontend_Contract */

        if ($contractHelper->contractsDisabled()) {
            return false;
        }

        $contracts = $this->getContracts();
        if (count($contracts) <= 1) {
            return false;
        }

        return true;
    }

    /**
     * Returns whether No Contract Selected option should be shown
     * 
     * @return boolean
     */
    public function showNoContractOption()
    {
        $contractHelper = $this->listsFrontendContractHelper;
        /* @var $contractHelper Epicor_Lists_Helper_Frontend_Contract */

        $requiredType = $contractHelper->requiredContractType();

        return in_array($requiredType, array('E', 'O'));
    }

    /**
     * Checks to see if we need an onclick on the change contract button
     * 
     * @return string
     */
    public function addOnClick()
    {
        $onClick = '';
        $quote = $this->checkoutCart->getQuote();
        /* @var $quote Epicor_Comm_Model_Quote */
        if ($quote->hasItems()) {
            $message = __('Changing Contract may remove items from the cart that are not valid for the selected Contract. Do you wish to continue?');
            $onClick = ' onclick="return confirm(\'' . $message . '\');" ';
        }

        return $onClick;
    }

}
