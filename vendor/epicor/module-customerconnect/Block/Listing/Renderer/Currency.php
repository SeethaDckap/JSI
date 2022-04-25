<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Listing\Renderer;


/**
 * Currency display, converts a row value to currency display
 *
 * @author Gareth.James
 */
class Currency extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;

    protected $dealerHelper;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        \Epicor\Dealerconnect\Helper\Data $dealerHelper,
        \Magento\Framework\App\Request\Http $request,
        array $data = []
    ) {
        $this->commMessagingHelper = $commMessagingHelper;
        $this->dealerHelper = $dealerHelper;
        $this->request = $request;
        parent::__construct(
            $context,
            $data
        );
    }


    public function render(\Magento\Framework\DataObject $row)
    {
        $module = $this->request->getModuleName();
        $controllerName = $this->request->getControllerName();
        $helper = $this->commMessagingHelper;

        $isDealer = $helper->customerSessionFactory()->getCustomer()->isDealer();
        $loginMode = $this->dealerHelper->checkCustomerLoginModeType();

        $index = $this->getColumn()->getIndex();
        $currency = $helper->getCurrencyMapping($row->getCurrencyCode(), \Epicor\Comm\Helper\Messaging::ERP_TO_MAGENTO);

        if ($module === "dealerconnect"
            && $controllerName == "dashboard"
            && $index == 'original_value') {
            $rowAttributes = $row->getData('_attributes');
            $isDealerRow = !is_null($rowAttributes) ? $rowAttributes->getDealer() : 'N';
            if ($isDealerRow == 'Y'
                && $loginMode == 'shopper'
            ) {
                return $helper->formatPrice($row->getData('dealer_grand_total_inc'), true, $currency);
            }
        }

        if ($isDealer && $this->dealerHelper->isDealerPortal() && $index == 'dealer_grand_total_inc') {
            $dealer = $row->getData('dealer');
            $dealerAttributes = !is_null($dealer) ? $dealer->getData('_attributes') : null;
            $changedByErp = !is_null($dealerAttributes) ?  $dealerAttributes->getChangedByErp() : 'N';
            if ($changedByErp == 'Y') {
                return;
            }
        }

        return $helper->formatPrice($row->getData($index), true, $currency);
    }

}
