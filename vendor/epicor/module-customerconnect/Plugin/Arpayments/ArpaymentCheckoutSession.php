<?php

namespace Epicor\Customerconnect\Plugin\Arpayments;

class ArpaymentCheckoutSession
{

    protected $arpaymentsHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    protected $quoteRepository;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
        \Epicor\Customerconnect\Helper\Arpayments $arpaymentsHelper,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\Session $customerSession
    )
    {
        $this->arpaymentsHelper = $arpaymentsHelper;
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->quoteRepository = $quoteRepository;
        $this->registry = $registry;
    }

    public function afterGetQuote(\Magento\Checkout\Model\Session $subject, $result)
    {
        $arPaymentsPage = $this->arpaymentsHelper->checkArpaymentsPage();
        if ($arPaymentsPage) {
            $sessionQuote = $this->arpaymentsHelper->getArpaymentsSessionQuoteId();


            $this->registry->unregister('QuantityValidatorObserver');
            $this->registry->register('QuantityValidatorObserver', 1);
            $quote = $this->quoteRepository->get($sessionQuote);
            $this->registry->unregister('QuantityValidatorObserver');
            return $quote;
        } else {
            return $result;
        }
    }

    public function afterGetQuoteId(\Magento\Checkout\Model\Session $subject, $result)
    {
        $arPaymentsPage = $this->arpaymentsHelper->checkArpaymentsPage();
        if ($arPaymentsPage) {
            $sessionQuote = $this->arpaymentsHelper->getArpaymentsSessionQuoteId();
            return $sessionQuote;
        } else {
            return $result;
        }
    }

    /**
     * @param int $quoteId
     * @return void
     * @codeCoverageIgnore
     */
    public function aroundSetQuoteId(\Magento\Checkout\Model\Session $subject, \Closure $proceed, $quoteId)
    {
        //if ($quoteId) {
        $arPaymentsPage = $this->arpaymentsHelper->checkArpaymentsPage();
        $checkArpaymentsquote = false;
        if ($quoteId) {

            $this->registry->unregister('QuantityValidatorObserver');
            $this->registry->register('QuantityValidatorObserver', 1);
            $quote = $this->quoteRepository->get($quoteId);
            $this->registry->unregister('QuantityValidatorObserver');
            if ($quote->getArpaymentsQuote()) {
                $checkArpaymentsquote = true;
            }
        }

        if (!$arPaymentsPage || !$checkArpaymentsquote) {
            $proceed($quoteId);
        }
        //}
    }

}
