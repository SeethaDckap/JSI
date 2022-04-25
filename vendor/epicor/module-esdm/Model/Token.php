<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Esdm\Model;



class Token extends \Magento\Framework\Model\AbstractModel 
{

    protected $customerSession;


    protected $tokenCollectionFactory;    

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $salesOrderFactory;

    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $quoteQuoteFactory;    

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;    

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Sales\Model\OrderFactory $salesOrderFactory,
        \Magento\Quote\Model\QuoteFactory $quoteQuoteFactory,
        \Epicor\Esdm\Model\ResourceModel\Token\CollectionFactory $tokenCollectionFactory,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->salesOrderFactory = $salesOrderFactory;
        $this->quoteQuoteFactory = $quoteQuoteFactory;
        $this->urlBuilder = $urlBuilder;
        $this->customerSession = $customerSession;
        $this->tokenCollectionFactory = $tokenCollectionFactory;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }


    public function _construct()
    {

        $this->_init('Epicor\Esdm\Model\ResourceModel\Token');
    }    


    /**
     * Creates a token record with the provided info
     * 
     * @param string $ccvToken
     * @param string $cvvToken
     * @param \Magento\Framework\DataObject $payment
     * @param int $customer_id
     * 
     * @return Epicor_Esdm_Model_Token
     */
    public function createToken($ccvToken, $cvvToken, $payment = null, $customer_id = null)
    {

        if (!empty($ccvToken) && strlen($ccvToken) == 54) {
            if (!empty($cvvToken) && strlen($cvvToken) == 54) {

                if (!$customer_id) {
                   $customer_id = $this->customerSession->getCustomer()->getId();
                }
                // this bit added to allow for the same card to be used on multiple accounts 
                $customerToken = $this->tokenCollectionFactory->create();
                $customerToken->addFieldToFilter('customer_id', $customer_id);
                $customerToken->addFieldToFilter('ccv_token', $ccvToken);
                $token = $customerToken->getFirstItem();

                if (!$token->isObjectNew()) {
                    $this->load($token->getEntityId());
                }

                $this->setSuccess(true);

                $reusable_token = ($payment->getSaveCard() =="true") ? 1 : 0;

                if (!$this->getEntityId()) {
                    $this->getEntityId(null);
                    $this->setCcvToken($ccvToken);
                    $this->setCvvToken($cvvToken);
                    $this->setLastFour(substr($payment->getCcNumber(), -4));
                    $this->setCardType($payment->getCcType());
                    $this->setExpiryDate(strtotime($payment->getCcExpYear() . '-' . $payment->getCcExpMonth() . '-01'));
                    $this->setCustomerId($customer_id);
                    $this->setReuseable($reusable_token);
                    $this->save();
                } elseif ($reusable_token && !$this->getReuseable()) {
                    $this->setExpiryDate(strtotime($payment->getCcExpYear() . '-' . $payment->getCcExpMonth() . '-01'));
                    $this->setReuseable($reusable_token);
                    $this->save();
                } else {
                    $this->setCvvToken($cvvToken);
                }
            } else {
                $this->setErrormsg($cvvToken);
                $this->setSuccess(false);
            }
        } else {
            $this->setErrormsg($ccvToken);
            $this->setSuccess(false);
        }
        return $this;
    }    
}
