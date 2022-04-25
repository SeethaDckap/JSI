<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Masquerade;

class Masquerade extends \Epicor\Comm\Controller\Masquerade
{

    /**
     * @var \Epicor\BranchPickup\Helper\Data
     */
    protected $branchPickupHelper;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Cart $checkoutCart,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Customer\Model\SessionFactory $customerSessionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Url\DecoderInterface $decoder,
        \Magento\Quote\Model\Quote\AddressFactory $quoteQuoteAddressFactory,
        \Epicor\BranchPickup\Helper\Data $branchPickupHelper
    ) {
        $this->branchPickupHelper = $branchPickupHelper;
        parent::__construct(
            $context,
            $checkoutCart,
            $checkoutSession,
            $commHelper,
            $customerSessionFactory,
            $customerSession,
            $decoder,
            $quoteQuoteAddressFactory
        );
    }



    public function execute()
    {
        if ($data = $this->getRequest()->getParams()) {
            $customerSession = $this->customerSession;
            /* @var $customerSession \Magento\Customer\Model\Session */

            $customer = $customerSession->getCustomer();
            /* @var $customer \Epicor\Comm\Model\Customer */
            $helper = $this->commHelper;
            /* @var $helper \Epicor\Comm\Helper\Data */

            // reset branch pickup data
            $this->branchPickupHelper->selectBranchPickup(null);
            $this->branchPickupHelper->resetBranchLocationFilter();

            if (isset($data['masquerade_as'])) {
                if ($customer->canMasqueradeAs($data['masquerade_as'])) {
                    $helper->startMasquerade($data['masquerade_as']);
                    if (isset($data['cart_action'])) {
                        $customerSession->setB2BHierarchyMasquerade(true);
                        $this->checkoutSession->setCustomerData($customerSession->getCustomerData());
                        $this->_processCart($data['cart_action']);
                    }
                } else {
                    $this->messageManager->addErrorMessage(__('You are not allowed to masquerade as this ERP Account'));
                }
            } else {
                $helper->stopMasquerade();
                if (isset($data['cart_action'])) {
                    $this->_processCart($data['cart_action']);
                }
            }
            if (isset($data['isAjax']) && $data['isAjax'] == "true") {
                $result = array(
                    'type' => 'success'
                );
                $this->getResponse()->setHeader('Content-type', 'application/json');
                $this->getResponse()->setBody(json_encode($result));
            } else {
                $this->_redirect($this->decoder->decode($data['return_url']));
            }
        } else {
            exit;
            $this->_redirect('*/*/index');
        }
    }

    }
