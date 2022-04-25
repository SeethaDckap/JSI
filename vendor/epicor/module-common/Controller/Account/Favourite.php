<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Common\Controller\Account;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Customer\Model\Account\Redirect as AccountRedirect;

class Favourite extends \Magento\Customer\Controller\Account\Index
{

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $checkoutCart;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $customerSessionFactory;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Quote\Model\Quote\AddressFactory
     */
    protected $quoteQuoteAddressFactory;

    /**
     * @var \Magento\Framework\Url\DecoderInterface
     */
    protected $decoder;

    /**
     * @var AccountRedirect
     */
    protected $accountRedirect;

    /**
     * @var \Epicor\Comm\Model\Customer\ErpaccountFactory
     */
    protected $commCustomerErpaccountFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Checkout\Model\Cart $checkoutCart,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Customer\Model\SessionFactory $customerSessionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Url\DecoderInterface $decoder,
        \Magento\Quote\Model\Quote\AddressFactory $quoteQuoteAddressFactory,
        AccountRedirect $accountRedirect,
        \Epicor\Comm\Model\Customer\ErpaccountFactory $commCustomerErpaccountFactory
    )
    {
        $this->quoteQuoteAddressFactory = $quoteQuoteAddressFactory;
        $this->checkoutCart = $checkoutCart;
        $this->checkoutSession = $checkoutSession;
        $this->commHelper = $commHelper;
        $this->customerSessionFactory = $customerSessionFactory;
        $this->eventManager = $context->getEventManager();
        $this->customerSession = $customerSession;
        $this->decoder = $decoder;
        $this->commCustomerErpaccountFactory = $commCustomerErpaccountFactory;
        $this->accountRedirect = $accountRedirect;
        parent::__construct($context, $resultPageFactory);
    }

    public function execute()
    {
        if ($data = $this->getRequest()->getParams()) {
            $customerSession = $this->customerSession;
            /* @var $customerSession \Magento\Customer\Model\Session */

            $customer = $customerSession->getCustomer();
            $helper = $this->commHelper;
            /* @var $helper \Epicor\Comm\Helper\Data */

            if (isset($data['id'])) {
                if ($customer->isValidErpAccount($data['id'])) {
                    $customer->updateFavourite($data['id']);

                    $erpAccount = $this->commCustomerErpaccountFactory->create()->load($data['id']);
                    $this->messageManager->addSuccessMessage(__('%1 Company is set as Favourite Successfully',$erpAccount->getName()));
                } else {
                    $this->messageManager->addErrorMessage(
                        __('You are not allowed to Set Favourite as this ERP Account')
                    );
                }
            }elseif (isset($data['unselected'])) {
                $erpAccount = $this->commCustomerErpaccountFactory->create()->load($data['unselected']);
                $this->messageManager->addSuccessMessage(__('%1 Company was removed from Favourite Successfully',$erpAccount->getName()));
                $customer->unselectFavourite();
            }
            else {
                $this->messageManager->addErrorMessage(__('Invalid Data'));
            }
        }
        $this->_redirect($this->_url->getUrl('epicor/account/companylists'));
    }

}
