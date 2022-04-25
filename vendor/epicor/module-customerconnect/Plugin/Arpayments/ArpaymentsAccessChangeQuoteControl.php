<?php
namespace Epicor\Customerconnect\Plugin\Arpayments;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Quote\Model\Quote;
use Magento\Framework\Exception\StateException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;

class ArpaymentsAccessChangeQuoteControl
{
    /**
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * @param UserContextInterface $userContext
     */
    
    protected $arpaymentsHelper;
    
    public function __construct(
        UserContextInterface $userContext,
        \Epicor\Customerconnect\Helper\Arpayments $arpaymentsHelper
    ) {
        $this->userContext = $userContext;
        $this->arpaymentsHelper = $arpaymentsHelper;
    }

    /**
     * Checks if change quote's customer id is allowed for current user.
     *
     * @param CartRepositoryInterface $subject
     * @param Quote $quote
     * @throws StateException if Guest has customer_id or Customer's customer_id not much with user_id
     * or unknown user's type
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(CartRepositoryInterface $subject, CartInterface $quote)
    {
        if (!$this->isAllowed($quote)) {
            throw new StateException(__("Invalid state change requested"));
        }
    }

    /**
     * Checks if user is allowed to change the quote.
     *
     * @param Quote $quote
     * @return bool
     */
    private function isAllowed(Quote $quote)
    {
        
        $handle = $this->arpaymentsHelper->checkArpaymentsPage();
        if ($handle || $quote->getArpaymentsQuote()) {
            $isAllowed = true;
        }  else {
            switch ($this->userContext->getUserType()) {
                case UserContextInterface::USER_TYPE_CUSTOMER:
                    $isAllowed = ($quote->getCustomerId() == $this->userContext->getUserId());
                    break;
                case UserContextInterface::USER_TYPE_GUEST:
                    $isAllowed = ($quote->getCustomerId() === null);
                    break;
                case UserContextInterface::USER_TYPE_ADMIN:
                case UserContextInterface::USER_TYPE_INTEGRATION:
                    $isAllowed = true;
                    break;
                default:
                    $isAllowed = false;
            }            
        }       
        


        return $isAllowed;
    }
}
