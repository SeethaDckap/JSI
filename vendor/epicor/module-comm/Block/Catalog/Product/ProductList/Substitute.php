<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Epicor\Comm\Block\Catalog\Product\ProductList;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\App\ActionInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Url\Helper\Data;
/**
 * Catalog product upsell items block
 *
 * @api
 * @SuppressWarnings(PHPMD.LongVariable)
 * @since 100.0.2
 */
class Substitute extends \Magento\Catalog\Block\Product\AbstractProduct implements
    \Magento\Framework\DataObject\IdentityInterface
{

    /**
     * @var int
     */
    protected $_columnCount = 4;

    /**
     * @var  \Magento\Framework\DataObject[]
     */
    protected $_items;

    /**
     * @var Collection
     */
    protected $_itemCollection;

    /**
     * @var array
     */
    protected $_itemLimits = [];

    /**
     * Checkout session
     *
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * Catalog product visibility
     *
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $_catalogProductVisibility;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Epicor\Comm\Model\Substitute
     */
    protected $substitute;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $_http;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var Data
     */
    protected $urlHelper;

    /**
     * Substitute constructor.
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param Product\Visibility $catalogProductVisibility
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Epicor\Comm\Model\Substitute $substitute
     * @param \Magento\Framework\App\Request\Http $http
     * @param Data $urlHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Module\Manager $moduleManager,
        \Epicor\Comm\Model\Substitute $substitute,
        \Magento\Framework\App\Request\Http $http,
        Data $urlHelper,
        array $data = []
    ) {
        $this->registry = $context->getRegistry();
        $this->_http = $http;
        $this->_catalogProductVisibility = $catalogProductVisibility;
        $this->_checkoutSession = $checkoutSession;
        $this->moduleManager = $moduleManager;
        $this->substitute = $substitute;
        $this->urlHelper = $urlHelper;
        parent::__construct(
            $context,
            $data
        );
    }

    /**
     * Prepare data
     *
     * @return $this
     */
    protected function _prepareData()
    {
        if (!$this->isDisplayExist()) {
            return $this;
        }
        $product = $this->getProduct();
        /* @var $product \Epicor\Comm\Model\Product */
        $this->_itemCollection = $this->_getSubstituteProductCollection($product)->setPositionOrder()->addStoreFilter();
        if ($this->moduleManager->isEnabled('Magento_Checkout')) {
            $this->_addProductAttributesAndPrices($this->_itemCollection);
        }
        $this->_itemCollection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());

        $this->_itemCollection->load();

        /**
         * Updating collection with desired items
         */
        $this->_eventManager->dispatch(
            'catalog_product_substitute',
            ['product' => $product, 'collection' => $this->_itemCollection, 'limit' => null]
        );

        foreach ($this->_itemCollection as $product) {
            $product->setDoNotUseCategoryId(true);
        }

        return $this;
    }

    /**
     * Before to html handler
     *
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $this->_prepareData();
        return parent::_beforeToHtml();
    }

    /**
     * Get items collection
     *
     * @return Collection
     */
    public function getItemCollection()
    {
        /**
         * getIdentities() depends on _itemCollection populated, but it can be empty if the block is hidden
         * @see https://github.com/magento/magento2/issues/5897
         */
        if ($this->_itemCollection === null) {
            $this->_prepareData();
        }
        return $this->_itemCollection;
    }

    /**
     * Get collection items
     *
     * @return \Magento\Framework\DataObject[]
     */
    public function getItems()
    {
        if ($this->_items === null) {
            $this->_items = $this->getItemCollection()->getItems();
        }
        return $this->_items;
    }

    /**
     * Get row count
     *
     * @return float
     */
    public function getRowCount()
    {
        return ceil(count($this->getItemCollection()->getItems()) / $this->getColumnCount());
    }

    /**
     * Set column count
     *
     * @param string $columns
     * @return $this
     */
    public function setColumnCount($columns)
    {
        if ((int)$columns > 0) {
            $this->_columnCount = (int)$columns;
        }
        return $this;
    }

    /**
     * Get column count
     *
     * @return int
     */
    public function getColumnCount()
    {
        return $this->_columnCount;
    }

    /**
     * Reset items iterator
     *
     * @return void
     */
    public function resetItemsIterator()
    {
        $this->getItems();
        reset($this->_items);
    }

    /**
     * Get iterable item
     *
     * @return mixed
     */
    public function getIterableItem()
    {
        $item = current($this->_items);
        next($this->_items);
        return $item;
    }

    /**
     * Set how many items we need to show in upsell block
     *
     * Notice: this parameter will be also applied
     *
     * @param string $type
     * @param int $limit
     * @return \Magento\Catalog\Block\Product\ProductList\Upsell
     */
    public function setItemLimit($type, $limit)
    {
        if ((int)$limit > 0) {
            $this->_itemLimits[$type] = (int)$limit;
        }
        return $this;
    }

    /**
     * Get item limit
     *
     * @param string $type
     * @return array|int
     */
    public function getItemLimit($type = '')
    {
        if ($type == '') {
            return $this->_itemLimits;
        }
        if (isset($this->_itemLimits[$type])) {
            return $this->_itemLimits[$type];
        } else {
            return 0;
        }
    }

    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities()
    {
        $identities = [];
        if (!$this->isDisplayExist()) {
            return [];
        }
        foreach ($this->getItems() as $item) {
            // phpcs:ignore Magento2.Performance.ForeachArrayMerge
            $identities = array_merge($identities, $item->getIdentities());
        }
        return $identities;
    }

    /**
     * IsDisplayExist
     *
     * @return boolean
     */
    public function isDisplayExist()
    {
        $isSubstitute = $this->_scopeConfig->getValue(
            'epicor_comm_enabled_messages/msq_request/triggers_linked_products_substitute',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($isSubstitute) {
            return true;
        }

        return false;

    }//end isDisplayExist()


    public function getDisplayType()
    {
        return 'substitute';
    }

    public function getDisplayClass()
    {
        $actionName = $this->_getActionName();
        if ($actionName == "checkout_cart_index") {
            return 'crosssell substitute';
        }
        return 'upsell substitute';
    }

    public function getDisplayImagesClass()
    {
        $actionName = $this->_getActionName();
        if ($actionName == "checkout_cart_index") {
            return 'cart_cross_sell_products';
        }
        return 'upsell_products_list';
    }

    public function isCartView()
    {
        $actionName = $this->_getActionName();
        if ($actionName == "checkout_cart_index") {
            return true;
        }
        return false;
    }

    private function _getActionName()
    {
        $actionName = $this->_http->getModuleName() . "_" . $this->_http->getControllerName() . "_" . $this->_http->getActionName();
        return $actionName;
    }



    protected function _getSubstituteProductCollection($currentProduct = null)
    {
        return $this->substitute->getSubstituteProductCollection($currentProduct);
    }

    public function registry($key)
    {
        return $this->registry->registry($key);
    }

    /**
     * @param $key
     * @param $value
     * @param bool $graceful
     */
    public function register($key, $value, $graceful = false)
    {
        $this->registry->register($key, $value, $graceful);
    }

    /**
     * @param $key
     */
    public function unregister($key)
    {
        $this->registry->unregister($key);
    }

    public function getAddToCartPostParams(Product $product)
    {
        $url = $this->getAddToCartUrl($product);
        return [
            'action' => $url,
            'data' => [
                'product' => (int) $product->getEntityId(),
                ActionInterface::PARAM_NAME_URL_ENCODED => $this->urlHelper->getEncodedUrl($url),
            ]
        ];
    }


}
