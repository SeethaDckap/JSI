<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Block\Claims;


/**
 * Dealer Claims list
 *
 * @category   Epicor
 * @package    Epicor_Dealerconnect
 * @author     Epicor Websales Team
 * 
 */
class Listing extends \Epicor\Common\Block\Generic\Listing
{
    const FRONTEND_RESOURCE = 'Dealer_Connect::dealer_claim_read';
    /**
     * @var \Epicor\Common\Helper\Access
     */
    protected $commonAccessHelper;

    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Epicor\Common\Helper\Access $commonAccessHelper,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        array $data = []
    )
    {
        $this->commonAccessHelper = $commonAccessHelper;
        $this->commMessagingHelper = $commMessagingHelper;
        $this->storeManager = $context->getStoreManager();
        parent::__construct(
            $context,
            $data
        );
    }

    protected function _setupGrid()
    {
        $this->_controller = 'claims_listing';
        $this->_blockGroup = 'Epicor_Dealerconnect';
        $this->_headerText = __('Claims');

        $helper = $this->commonAccessHelper;

        $msgHelper = $this->commMessagingHelper;
        $enabled = $msgHelper->isMessageEnabled('dealerconnect', 'dcls');
        $erpAccount = $msgHelper->getErpAccountInfo();
        $currencyCode = $erpAccount->getCurrencyCode($this->storeManager->getStore()->getBaseCurrencyCode());
        
        if($this->_isAccessAllowed("Dealer_Connect::dealer_claim_create") && $enabled && $currencyCode) {
            $url = $this->getUrl('*/*/new/');
            $this->addButton(
                'new', array(
                'label' => __('New Claim'),
                'onclick' => 'setLocation(\'' . $url . '\')',
                'class' => 'add',
            ), 10
            );
        }
    }

}
