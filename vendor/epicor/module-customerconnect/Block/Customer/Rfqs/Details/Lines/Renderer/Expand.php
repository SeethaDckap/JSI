<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Rfqs\Details\Lines\Renderer;


/**
 * RFQ line row expand renderer
 * 
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Expand extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    const FRONTEND_RESOURCE_INFORMATION_READ = 'Epicor_Customerconnect::customerconnect_account_rfqs_misc';
    const FRONTEND_RESOURCE_INFORMATION_READ_DEALER = 'Dealer_Connect::dealer_quotes_misc';
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Epicor\Customerconnect\Helper\Data $customerHelper
     */
    protected $customerconnectHelper;

    /**
     * @var \Epicor\Dealerconnect\Helper\Data
     */
    protected $dealerHelper;

    /**
     * @var \Epicor\AccessRight\Model\Authorization
     */
    protected $_accessauthorization;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Epicor\Dealerconnect\Helper\Data $dealerHelper,
        array $data = []
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        $this->customerconnectHelper = $customerconnectHelper;
        $this->_accessauthorization = $context->getAccessAuthorization();
        $this->dealerHelper = $dealerHelper;
        parent::__construct(
            $context,
            $data
        );
    }


    public function render(\Magento\Framework\DataObject $row)
    {
        $canSupportAttachment = $this->scopeConfig->getValue('customerconnect_enabled_messages/CRQD_request/attachment_support', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $showMiscCharges = $this->canShowMisc();
        $defaultMiscView = $this->customerconnectHelper->checkCusMiscView();
        $miscellaneousCharges = $row->getMiscellaneousCharges() ? $row->getMiscellaneousCharges()->getasarrayMiscellaneousLine() : array();
        $canExpandMisc = (!empty($miscellaneousCharges) && $showMiscCharges && $row->getMiscellaneousChargesTotal());
        $expand = $defaultMiscView ? '-' : '+';
        if ($canSupportAttachment || $canExpandMisc) {
            $html = '<span class="plus-minus" type="quotes" uni='.$row->getUniqueId().' id="attachments-' . $row->getUniqueId() . '">'.$expand.'</span>';
        } else {
            $html = '';
        }
        return $html;
    }

    public function canShowMisc()
    {
        $showMiscCharges = $this->customerconnectHelper->showMiscCharges();
        $isDealer = $this->dealerHelper->isDealerPortal();
        $code = $isDealer ? static::FRONTEND_RESOURCE_INFORMATION_READ_DEALER : static::FRONTEND_RESOURCE_INFORMATION_READ;
        $isMiscAllowed = $this->_accessauthorization->isAllowed($code);
        return $showMiscCharges && $isMiscAllowed;
    }
}
