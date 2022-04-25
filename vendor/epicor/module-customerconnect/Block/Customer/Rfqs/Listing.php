<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Block\Customer\Rfqs;


/**
 * Customer RFQ list
 *
 * @category   Epicor
 * @package    Epicor_Supplierconnect
 * @author     Epicor Websales Team
 */
class Listing extends \Epicor\Common\Block\Generic\Listing
{

    const FRONTEND_RESOURCE = 'Epicor_Customerconnect::customerconnect_account_rfqs_read';

    const FRONTEND_RESOURCE_CREATE = 'Epicor_Customerconnect::customerconnect_account_rfqs_create';

    const ACCESS_MESSAGE_DISPLAY = TRUE;
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
        $this->_controller = 'customer_rfqs_listing';
        $this->_blockGroup = 'Epicor_Customerconnect';
        $this->_headerText = __('RFQs');

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
