<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Helper\Sales;

use Magento\Framework\App\RequestInterface;

class Order extends \Magento\Framework\App\Helper\AbstractHelper
{

    protected $_displayOrderNumber = array();

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerCustomerFactory;

    /**
     * Sales Rep helper.
     *
     * @var \Epicor\SalesRep\Helper\Data
     */
    private $salesRepHelper;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory,
        \Epicor\SalesRep\Helper\Data $salesRepHelper
    ) {
        $this->customerSession = $customerSession;
        $this->customerCustomerFactory = $customerCustomerFactory;
        $this->salesRepHelper = $salesRepHelper;
        parent::__construct($context);
    }
    public function showErpOrderNumber($order = null)
    {
        return in_array($this->getDisplayOrderNumber($order), array('erp', 'both'));
    }

    public function showWebOrderNumber($order = null)
    {
        return in_array($this->getDisplayOrderNumber($order), array('web', 'both'));
    }

    public function showErpOrderNumberOnly($order = null)
    {
        return ($this->getDisplayOrderNumber($order) == 'erp');
    }

    public function showWebOrderNumberOnly($order = null)
    {
        return ($this->getDisplayOrderNumber($order) == 'web');
    }

    public function showBothOrderNumbers($order = null)
    {
        return ($this->getDisplayOrderNumber($order) == 'both');
    }

    /*START of AR payment module related methods*/
        
    public function showErpArpaymentsNumber($order = null){
        return in_array($this->getDisplayArpaymentsNumber($order), array('erp', 'both'));
    }
    
    public function showWebArpaymentsNumber($order = null){
        return in_array($this->getDisplayArpaymentsNumber($order), array('web', 'both'));
    }
    
    public function showErpArpaymentsNumberOnly($order = null){
        return ($this->getDisplayArpaymentsNumber($order) == 'erp');
    }
    
    public function showWebArpaymentsNumberOnly($order = null){
        return ($this->getDisplayArpaymentsNumber($order) == 'web');
    }
    
    public function showBothArpaymentsNumbers($order = null){
        return ($this->getDisplayArpaymentsNumber($order) == 'both');
    } 
    /*END of AR payment module related methods */
    
    protected function getDisplayOrderNumber($order = null)
    {

        $orderId = $order instanceof \Magento\Sales\Model\Order ? $order->getId() : 0;

        if (!isset($this->_displayOrderNumber[$orderId])) {
            if ($orderId == 0) {
                $customer = $this->customerSession->getCustomer();
                /* @var $customer \Epicor\Comm\Model\Customer */
                $storeId = null;
            } else {
                $customer = $this->customerCustomerFactory->create()->load($order->getCustomerId());
                /* @var $customer \Epicor\Comm\Model\Customer */
                $storeId = $order->getStoreId();
            }

            $isSalesRepB2b=false;
            $isSalesRepB2c=false;
            if ($order && $customer->isSalesRep() && $order->getEccErpAccountId()) {
                $orderErpAccountId = $order->getEccErpAccountId();
                $helperSalesRep = $this->salesRepHelper;
                /**
                 * Get order ErpAccount type
                 * for placed SalesRep Masquerade ERP ID.
                 */
                $erpInfo = $helperSalesRep->getErpAccountInfo($orderErpAccountId);
                /* @var $erpInfo \Epicor\Comm\Model\Customer\Erpaccount */

                if ($erpInfo && $erpInfo->getAccountType() == "B2B") {
                    $isSalesRepB2b=true;
                } elseif ($erpInfo && $erpInfo->getAccountType() == "B2C") {
                    $isSalesRepB2c=true;
                }
            }

            if ($customer->isCustomer(false) || $isSalesRepB2b) {
                $this->_displayOrderNumber[$orderId] = $this->scopeConfig->getValue('sales/general/display_order_number_b2b', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
            } elseif ($customer->isGuest(false) || $isSalesRepB2c) {
                $this->_displayOrderNumber[$orderId] = $this->scopeConfig->getValue('sales/general/display_order_number_b2c', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
            }
        }
        if(isset($this->_displayOrderNumber[$orderId])){
            return $this->_displayOrderNumber[$orderId];
        }
        return [];
    }
    
    /**
     * 
     * @param \Mage\Sales\Model\Order\Shipment $shipment
     * @return void|string
     */
    public function getEccErpShipmentNumber($shipment)
    {
        $items = $shipment->getItems();
        foreach ($items as $item) {
            if ($eccErpShipmentNumber = $item->getEccErpShipmentNumber()) {
                return $eccErpShipmentNumber;
            }
        }
        return;
    }
    protected function getDisplayArpaymentsNumber($order = null){
        
        $orderId = $order instanceof \Magento\Sales\Model\Order ? $order->getId() : 0;
        
        if(!isset($this->_displayOrderNumber[$orderId])){
            if ($orderId == 0) {
                $customer = $this->customerSession->getCustomer();
                /* @var $customer Epicor_Comm_Model_Customer */
                $storeId = null;
            } else {
                $customer = $this->customerCustomerFactory->create()->load($order->getCustomerId());
                /* @var $customer Epicor_Comm_Model_Customer */
                $storeId = $order->getStoreId();
            }
            
            if ($customer->isCustomer(false)) {
                $this->_displayOrderNumber[$orderId] = $this->scopeConfig->getValue('sales/general/display_order_number_arpayments',  \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
            } 
        }
        $displayOrderNumber = isset($this->_displayOrderNumber[$orderId]) ? $this->_displayOrderNumber[$orderId]: "";
        return $displayOrderNumber;
    }


    /**
     * Get request.
     *
     * @return RequestInterface
     */
    public function getRequest()
    {
        return $this->_request;

    }//end getRequest()


}
