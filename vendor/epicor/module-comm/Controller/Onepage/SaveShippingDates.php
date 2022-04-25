<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Onepage;

class SaveShippingDates extends \Epicor\Comm\Controller\Onepage
{

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager
    ) {
        $this->eventManager = $eventManager;
    }
    public function execute()
    {
        $this->_expireAjax();
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost('shipping_dates', '');
            $result = $this->getOnepage()->saveShippingDates($data);
            /*
              $result will have error data if shipping method is empty
             */
            if (!$result) {
                $this->eventManager->dispatch('checkout_controller_onepage_save_shipping_dates', array('request' => $this->getRequest(), 'quote' => $this->getOnepage()->getQuote()));

                $result['goto_section'] = 'payment';
                $result['update_section'] = array(
                    'name' => 'payment-method',
                    'html' => $this->_getPaymentMethodsHtml()
                );
            }
            $this->getResponse()->setBody(\Zend_Json::encode($result));
        }
    }

    }
