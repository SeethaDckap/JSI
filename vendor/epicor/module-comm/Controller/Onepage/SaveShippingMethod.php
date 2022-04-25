<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Onepage;

class SaveShippingMethod extends \Epicor\Comm\Controller\Onepage
{

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        $this->eventManager = $eventManager;
        $this->scopeConfig = $scopeConfig;
        $this->jsonHelper = $jsonHelper;
    }
    public function execute()
    {
        if ($this->_expireAjax()) {
            return;
        }
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost('shipping_method', '');
            $result = $this->getOnepage()->saveShippingMethod($data);
            /*
              $result will have erro data if shipping method is empty
             */
            if (!$result) {
                $this->eventManager->dispatch('checkout_controller_onepage_save_shipping_method', array('request' => $this->getRequest(),
                    'quote' => $this->getOnepage()->getQuote()));

                $this->getOnepage()->getQuote()->setEccBsvCarriageAmount(null);
                $this->getOnepage()->getQuote()->setEccBsvCarriageAmountInc(null);

                $this->getOnepage()->getQuote()->collectTotals()->save();

                if ($this->scopeConfig->isSetFlag('epicor_comm_enabled_messages/dda_request/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
                    $result['goto_section'] = 'shipping_dates';
                    $result['update_section'] = array(
                        'name' => 'shipping_dates',
                        'html' => $this->_getShippingDatesHtml()
                    );
                } else {
                    $result['goto_section'] = 'payment';
                    $result['update_section'] = array(
                        'name' => 'payment-method',
                        'html' => $this->_getPaymentMethodsHtml()
                    );
                }
            } else {
                $this->getOnepage()->getQuote()->collectTotals()->save();
            }
            //M1 > M2 Translation Begin (Rule p2-7)
            //$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            $this->getResponse()->setBody($this->jsonHelper->jsonEncode($result));
            //M1 > M2 Translation End
        }
    }

    }
