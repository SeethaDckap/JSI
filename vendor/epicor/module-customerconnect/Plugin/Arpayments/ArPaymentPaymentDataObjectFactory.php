<?php
namespace Epicor\Customerconnect\Plugin\Arpayments;

use Magento\Framework\ObjectManagerInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\Sales\Model\Order\Payment;

class ArPaymentPaymentDataObjectFactory
{
    
    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     */
    private $objectManager;
    
    /**
     * @var Order\OrderAdapterFactory
     */
    private $orderAdapterFactory;
    
    /**
     * @var Quote\QuoteAdapterFactory
     */
    private $quoteAdapterFactory;
    
    /**
     * Factory constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @param Order\OrderAdapterFactory $orderAdapterFactory
     * @param Quote\QuoteAdapterFactory $quoteAdapterFactory
     */
    public function __construct(ObjectManagerInterface $objectManager,
                                \Magento\Payment\Gateway\Data\Order\OrderAdapterFactory $orderAdapterFactory,
                                \Magento\Payment\Gateway\Data\Quote\QuoteAdapterFactory $quoteAdapterFactory)
    {
        $this->objectManager       = $objectManager;
        $this->orderAdapterFactory = $orderAdapterFactory;
        $this->quoteAdapterFactory = $quoteAdapterFactory;
    }
    /**
     * @param int $quoteId
     * @return void
     * @codeCoverageIgnore
     */
    public function aroundCreate(\Magento\Payment\Gateway\Data\PaymentDataObjectFactory $subject, 
                                 \Closure $proceed, 
                                 InfoInterface $paymentInfo)
    {
        $getArpayment = 0;
        if ($paymentInfo instanceof Payment) {
            $data['order'] = $this->orderAdapterFactory->create(array(
                'order' => $paymentInfo->getOrder()
            ));
        } elseif ($paymentInfo instanceof \Magento\Quote\Model\Quote\Payment) {
            $data['order'] = $this->quoteAdapterFactory->create(array(
                'quote' => $paymentInfo->getQuote()
            ));
        } elseif ($paymentInfo instanceof \Epicor\Customerconnect\Model\ArPayment\Order\Payment) {
            return;
        } elseif ($paymentInfo instanceof \Epicor\Customerconnect\Model\ArPayment\Quote\Payment) {
            return;
        }
        
        if ($getArpayment) {
            return;
        } else {
            $data['payment'] = $paymentInfo;
            return $this->objectManager->create(\Magento\Payment\Gateway\Data\PaymentDataObject::class, $data);
        }
    }
    
}