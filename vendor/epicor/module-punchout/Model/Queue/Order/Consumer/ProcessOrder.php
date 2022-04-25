<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Punchout
 * @subpackage Model
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Punchout\Model\Queue\Order\Consumer;

use Epicor\Punchout\Model\Connections;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Quote\Model\QuoteManagement;
use Magento\Store\Model\StoreManagerInterface;
use Epicor\Punchout\Model\ChangeHandler;
use Epicor\Punchout\Api\ConnectionsRepositoryInterface;
use Epicor\Punchout\Api\Data\Order\PurchaseOrderInterface;
use Epicor\Punchout\Model\QuoteHandler;
use Epicor\Punchout\Model\AddressHandler;

/**
 * Consumer Queue For Punchout order process.
 * Call Via Magento Queue Mechanism.
 */
class ProcessOrder
{

    /**
     * Registry.
     *
     * @var Registry
     */
    private $registry;

    /**
     * Connection repository interface.
     *
     * @var ConnectionsRepositoryInterface
     */
    private $connectionRepository;

    /**
     * Store manager interface.
     *
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Customer repository.
     *
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * Quote to order model.
     *
     * @var QuoteHandler
     */
    private $quoteHandler;

    /**
     * Quote address handler.
     *
     * @var AddressHandler
     */
    private $addressHandler;

    /**
     * @var QuoteManagement
     */
    private $quoteManagement;

    /**
     * @var mixed
     */
    private $errors = [];


    /**
     * Consumer constructor.
     *
     * @param Registry    $registry             Registry.
     * @param ConnectionsRepositoryInterface $connectionRepository Customer repository.
     * @param StoreManagerInterface          $storeManager         Store manager.
     * @param CustomerRepositoryInterface    $customerRepository   Customer repsitory.
     * @param QuoteHandler                   $quoteHandler         Quote to order model.
     * @param AddressHandler                 $addressHandler       Address handler.
     * @param QuoteManagement                $quoteManagement      Quote management.
     */
    public function __construct(
        Registry $registry,
        ConnectionsRepositoryInterface $connectionRepository,
        StoreManagerInterface $storeManager,
        CustomerRepositoryInterface $customerRepository,
        QuoteHandler $quoteHandler,
        AddressHandler $addressHandler,
        QuoteManagement $quoteManagement
    ) {
        $this->registry             = $registry;
        $this->connectionRepository = $connectionRepository;
        $this->storeManager         = $storeManager;
        $this->customerRepository   = $customerRepository;
        $this->quoteHandler         = $quoteHandler;
        $this->addressHandler       = $addressHandler;
        $this->quoteManagement      = $quoteManagement;

    }//end __construct()


    /**
     * Consumer Logic. Process the PO instance to place an order.
     *
     * @param PurchaseOrderInterface $orderInstance PO instance.
     *
     * @return void
     */
    public function process(PurchaseOrderInterface $orderInstance)
    {
        $order                     = null;
        $proceedWithIdentification = false;

        try {
            $connectionId  = $orderInstance->getConnectionId();
            $connection    = $this->connectionRepository->loadEntity($connectionId);
            $storeId       = $this->getStoreId($connection);
            $store         = $this->storeManager->getStore($storeId);
            $customer      = $this->customerRepository->getById($orderInstance->getCustomerId());

            $proceedWithIdentification = true;

            if (!$customer->getId()) {
                $this->errors = ['message' => ChangeHandler::CUSTOMER_ERROR];
            } else {
                //To fix issue with registry getting shared when in cron.
                $this->registry->__destruct();

                //To prevent addition BSV to go while creating cart.
                if (!$this->registry->registry('bsv_sent')) {
                    $this->registry->register('bsv_sent', true);
                }
                $quote = $this->quoteHandler->updateQuote($customer, $orderInstance, $store);

                if (!$quote->hasItems()) {
                    $this->errors = ['message' => ChangeHandler::EMPTY_QUOTE_ERROR];
                    $proceedWithIdentification = false;
                } else {
                    $quote->setIsPunchout(1);
                    $quote->setEccPunchoutConnectionId($connectionId);

                    $quote = $this->addressHandler->updateAddresses($quote, $orderInstance->getShippingAddressCode(), $customer->getId());

                    //Add shipping method.
                    $shippingAddress = $quote->getShippingAddress();
                    $method          = $orderInstance->getMethodCode();
                    $method          = json_decode($method[0], true);
                    $shippingAddress->setShippingMethod('punchout_carrier_punchout_carrier');
                    $quote->setPunchoutShippingCode($method['code']);
                    $quote->setPunchoutShippingAmount($method['amt']);
                    $quote->setShippingAddress($shippingAddress);

                    //add payment method type
                    $quote->setPaymentMethod('pay');
                    $quote->getPayment()->importData(['method' => 'pay']);
                    $quote->getShippingAddress()->setShippingMethod('punchout_carrier_punchout_carrier');
                    $quote->getShippingAddress()->setCollectShippingRates(true);
                    $quote->getShippingAddress()->collectShippingRates();

                    //send punchout order reference no
                    $quote->setEccCustomerOrderRef($orderInstance->getOrderId());

                    //add price change handler
                    $quote->save();
                    $quote->collectTotals();

                    //convert quote to order
                    if (!$quote->hasItems()) {
                        $this->errors = ['message' => ChangeHandler::EMPTY_QUOTE_ERROR];
                        $proceedWithIdentification = false;
                    } else {
                        $order = $this->quoteManagement->submit($quote);
                        $this->quoteHandler->saveOrderRef($order, $orderInstance->getOrderId());
                    }
                }
            }
        } catch (\Exception $e) {
            $this->errors[] = ChangeHandler::TECHNICAL_ERROR;

            $this->errors['exception'] = $e->getMessage();
        }//end try

        //Manage differences
        $this->quoteHandler->ManageErrors($order, $orderInstance, $this->errors, $proceedWithIdentification);

    }//end process()


    /**
     * Get store ID.
     *
     * @param Connections $connection Connection.
     *
     * @return int|string
     * @throws NoSuchEntityException
     */
    private function getStoreId(Connections $connection)
    {
        if ($connection->getStoreId() == 0 && $connection->getWebsiteId() == 0) {
            return $this->storeManager->getStore()->getId();
        } else {
            return $connection->getStoreId();
        }

    }//end getStoreId()


}//end class

