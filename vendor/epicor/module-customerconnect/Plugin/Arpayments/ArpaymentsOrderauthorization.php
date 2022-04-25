<?php
namespace Epicor\Customerconnect\Plugin\Arpayments;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class ArpaymentsOrderauthorization
{
    
    /**
     * @var UserContextInterface
     */
    protected $userContext;
    
    
    protected $_request;
    
    protected $arpaymentsHelper;
    
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;    
    
    /**
     * @param UserContextInterface $userContext
     */
    public function __construct(\Magento\Authorization\Model\UserContextInterface $userContext, 
                                \Magento\Customer\Model\Session $customerSession,
                                \Epicor\Customerconnect\Helper\Arpayments $arpaymentsHelper, 
                                \Magento\Framework\App\Request\Http $request)
    {
        $this->userContext      = $userContext;
        $this->_request         = $request;
        $this->arpaymentsHelper = $arpaymentsHelper;
        $this->customerSession = $customerSession;
    }
    
    /**
     * Checks if order is allowed
     *
     * @param \Magento\Sales\Model\ResourceModel\Order $subject
     * @param callable $proceed
     * @param \Magento\Framework\Model\AbstractModel $order
     * @param mixed $value
     * @param null|string $field
     * @return \Magento\Sales\Model\Order
     * @throws NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundLoad(\Magento\Sales\Model\ResourceModel\Order $subject, 
                                \Closure $proceed, \Magento\Framework\Model\AbstractModel $order, 
                                $value, $field = null)
    {
        $result = $proceed($order, $value, $field);
        if (!$this->isAllowed($order)) {
            throw NoSuchEntityException::singleField('orderId', $order->getId());
        }
        return $result;
    }
    
    /**
     * Checks if order is allowed for current customer
     *
     * @param \Magento\Sales\Model\Order $order
     * @return bool
     */
    protected function isAllowed(\Magento\Sales\Model\Order $order)
    {
        $handle = $this->arpaymentsHelper->checkArpaymentsPage();
        if ($handle || $order->getArpaymentsQuote()) {
            $order->setCanSendNewEmailFlag(false);
            return true;
        } else {
            $customer = $this->customerSession->getCustomer();
            if ($customer->isSalesRep()) {
                return true;
            } else {
                return $this->userContext->getUserType() == UserContextInterface::USER_TYPE_CUSTOMER ? $order->getCustomerId() == $this->userContext->getUserId() : true;
            }
        }
    }
}