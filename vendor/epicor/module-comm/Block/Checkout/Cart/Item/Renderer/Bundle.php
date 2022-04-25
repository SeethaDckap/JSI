<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Checkout\Cart\Item\Renderer;


/**
 * Shopping cart item render block
 *
 * @category    Mage
 * @package     Mage_Bundle
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Bundle extends \Magento\Bundle\Block\Checkout\Cart\Item\Renderer
{

    /**
     * @var \Magento\GroupedProduct\Model\Product\Type\GroupedFactory
     */
    protected $groupedProductProductTypeGroupedFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $catalogProductFactory;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Helper\Product\Configuration $productConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\View\Element\Message\InterpretationStrategyInterface $messageInterpretationStrategy,
        \Magento\Bundle\Helper\Catalog\Product\Configuration $bundleProductConfiguration,
        \Magento\GroupedProduct\Model\Product\Type\GroupedFactory $groupedProductProductTypeGroupedFactory,
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory,
        array $data = []
    ) {
        $this->groupedProductProductTypeGroupedFactory = $groupedProductProductTypeGroupedFactory;
        $this->catalogProductFactory = $catalogProductFactory;
        parent::__construct(
            $context,
            $productConfig,
            $checkoutSession,
            $imageBuilder,
            $urlHelper,
            $messageManager,
            $priceCurrency,
            $moduleManager,
            $messageInterpretationStrategy,
            $bundleProductConfiguration,
            $data
        );
    }


    public function getFormatedOptionValue($optionValue)
    {
        $result = parent::getFormatedOptionValue($optionValue);

        $result['value'] = preg_replace('/<span class="price">.*<\/span>/', '', $result['value']);

        return $result;
    }

    public function isGroupProduct($product)
    {
        if ($product->getTypeId() == "bundle") {
            $parentIds = $this->groupedProductProductTypeGroupedFactory->create()->getParentIdsByChild($product->getId());
            if ($parentIds) {
                $parentProduct = $this->catalogProductFactory->create()->load($parentIds);
                return $parentIds[0];
            }
        }
    }

    public function getProductUrlPath($id)
    {
        $product = $this->catalogProductFactory->create()->load($id);
        return $product->getUrlPath();
    }

    /**
     * Return cart item error messages
     *
     * @return array
     */
    public function getMessages()
    {
        $messages = array();
        $quoteItem = $this->getItem();

        // Add basic messages occuring during this page load
        $baseMessages = $quoteItem->getMessage(false);
        if ($baseMessages) {
            foreach ($baseMessages as $message) {
                $messages[] = array(
                    'text' => $message,
                    'type' => $quoteItem->getHasError() ? 'error' : 'notice'
                );
            }
        }


        // Add messages saved previously in checkout session
        $checkoutSession = $this->getCheckoutSession();
        if ($checkoutSession) {
            /* @var $collection Mage_Core_Model_Message_Collection */
            $collection = $checkoutSession->getQuoteItemMessages($quoteItem->getId(), true);
            if ($collection) {
                $additionalMessages = $collection->getItems();
                foreach ($additionalMessages as $message) {
                    /* @var $message Mage_Core_Model_Message_Abstract */
                    $messages[] = array(
                        'text' => $message->getCode(),
                        'type' => ($message->getType() == \Magento\Framework\Message\Factory::ERROR) ? 'error' : 'notice'
                    );
                }
            }
        }

        return $messages;
    }

    //M1 > M2 Translation Begin (Rule 56)
    /**
     * @param $path
     * @return mixed
     */
    public function getConfig($path)
    {
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param $path
     * @return bool
     */
    public function getConfigFlag($path)
    {
        return $this->_scopeConfig->isSetFlag($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    //M1 > M2 Translation End
}
