<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Elements\Block\Checkout;
/**
 * Elements Payment  
 * 
 * @category    Epicor
 * @package     Epicor_Elements
 * @author      Epicor Web Sales Team
 */

class Setupreturn extends \Magento\Framework\View\Element\Template
{
    
    protected $_transaction;
    
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    
    public function __construct(\Magento\Framework\View\Element\Template\Context $context, \Magento\Framework\Registry $registry,array $data = [])
    {
        $this->registry = $registry;
        parent::__construct($context, $data);
    }
    
    
    /**
     * 
     * @return \Epicor\Elements\Model\Transaction
     */
    public function getTransaction()
    {
        if (!$this->_transaction)
            $this->_transaction = $this->registry->registry('elements_transaction');
        return $this->_transaction;
    }
    
    /**
     * Check if Hosted Card Capture was successful
     * 
     * @return bool
     */
    public function isSuccessful()
    {
        $val = false;
        if ($this->getTransaction()) {
            $val = $this->getTransaction()->successfulHostedResponse() && $this->isCardExpiryValid();
        }
        return $val;
    }
    
    public function isCardExpiryValid()
    {
        $val = false;
        if ($this->getTransaction()) {
            $val = $this->getTransaction()->isCardExpiryValid();
        }

        return $val;
    }

    public function hasExpiryDate()
    {
        $val = false;
        if ($this->getTransaction()) {
            $val = $this->getTransaction()->getExpirationMonth() != null && $this->getTransaction()->getExpirationYear() != null;
        }

        return $val;
    }
    
    /**
     * 
     * @return bool
     */
    public function isCancelled()
    {
        $val = false;
        if ($this->getTransaction())
            $val = $this->getTransaction()->getHostedPaymentStatus() == 'Cancelled';
        
        return $val;
    }
    
}
