<?php

namespace Silk\CustomAccount\Model\Quote\Item;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartItemRepositoryInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartItemInterfaceFactory;
use Magento\Quote\Model\Quote\Item\CartItemOptionsProcessor;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Epicor\Comm\Helper\Data as EpicorHelper;

class Repository extends \Magento\Quote\Model\Quote\Item\Repository
{
    /**
     * Quote repository.
     *
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * Product repository.
     *
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var CartItemInterfaceFactory
     */
    protected $itemDataFactory;

    /**
     * @var CartItemProcessorInterface[]
     */
    protected $cartItemProcessors;

    /**
     * @var CartItemOptionsProcessor
     */
    private $cartItemOptionsProcessor;

    private $registry;

    public function __construct(
        CartRepositoryInterface $quoteRepository,
        ProductRepositoryInterface $productRepository,
        CartItemInterfaceFactory $itemDataFactory,
        CartItemOptionsProcessor $cartItemOptionsProcessor,
        \Magento\Framework\Registry $registry,
        array $cartItemProcessors = []
    ) {
        parent::__construct($quoteRepository, $productRepository, $itemDataFactory, $cartItemOptionsProcessor, $cartItemProcessors);
        $this->cartItemOptionsProcessor = $cartItemOptionsProcessor;
        $this->registry = $registry;
    }

    public function save(\Magento\Quote\Api\Data\CartItemInterface $cartItem)
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/observer.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);

        $logger->info('Trigger cart item save');

        $cartId = $cartItem->getQuoteId();
        if (!$cartId) {
            throw new InputException(
                __('"%fieldName" is required. Enter and try again.', ['fieldName' => 'quoteId'])
            );
        }

        $quote = $this->quoteRepository->getActive($cartId);
        
        $quoteItems = $quote->getItems();
        $quoteItems[] = $cartItem;
        $quote->setItems($quoteItems);
        $this->registry->register('not_collect_kuebix', true);
        $logger->info('Repository quote save');
        $this->quoteRepository->save($quote);
        $logger->info('Repository quote collect total');
        $quote->collectTotals();
        return $quote->getLastAddedItem();
    }

    public function deleteById($cartId, $itemId)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        $quoteItem = $quote->getItemById($itemId);
        if (!$quoteItem) {
            throw new NoSuchEntityException(
                __('The %1 Cart doesn\'t contain the %2 item.', $cartId, $itemId)
            );
        }
        try {
            $quote->removeItem($itemId);
            $quote->setTotalsCollectedFlag(true);
            $this->registry->register('not_collect_kuebix', true);
            $this->quoteRepository->save($quote);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__("The item couldn't be removed from the quote."));
        }

        return true;
    }
}
