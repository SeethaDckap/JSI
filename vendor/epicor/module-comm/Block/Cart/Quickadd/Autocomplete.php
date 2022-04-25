<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Cart\Quickadd;

/**
 * Autocomplete queries list
 */
class Autocomplete extends \Magento\Framework\View\Element\AbstractBlock
{

    protected $_suggestData = null;
    protected $_lastRowId = null;
    protected $_lastGroupChildData = null;
    protected $_enteredSku = null;
    protected $_pageData = null;
    protected $_page = 1;
    protected $_rowCount = 0;
    protected $_lastChildId = 0;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Epicor\Lists\Helper\Frontend\Contract
     */
    protected $listsFrontendContractHelper;

    /**
     * @var \Epicor\Lists\Helper\Frontend\Product
     */
    protected $listsFrontendProductHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Epicor\Comm\Helper\Locations
     */
    protected $commLocationsHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $catalogResourceModelProductCollectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Epicor\Comm\Helper\Data $commHelper,
        \Epicor\Lists\Helper\Frontend\Contract $listsFrontendContractHelper,
        \Epicor\Lists\Helper\Frontend\Product $listsFrontendProductHelper,
        \Epicor\Comm\Helper\Locations $commLocationsHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $catalogResourceModelProductCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Request\Http $request,
        array $data = []
    ) {
        $this->commHelper = $commHelper;
        $this->listsFrontendContractHelper = $listsFrontendContractHelper;
        $this->listsFrontendProductHelper = $listsFrontendProductHelper;
        $this->scopeConfig = $context->getScopeConfig();
        $this->commLocationsHelper = $commLocationsHelper;
        $this->registry = $registry;
        $this->resourceConnection = $resourceConnection;
        $this->catalogResourceModelProductCollectionFactory = $catalogResourceModelProductCollectionFactory;
        $this->storeManager = $storeManager;
        $this->request = $request;
        parent::__construct(
            $context,
            $data
        );
    }


    protected function _toHtml()
    {
        $html = '';

        if (!$this->_beforeToHtml()) {
            return $html;
        }

        $helper = $this->commHelper;
        /* @var $helper Epicor_Comm_Helper_Data */
        $contractHelper = $this->listsFrontendContractHelper;
        /* @var $contractHelper Epicor_Lists_Helper_Frontend_Contract */
        $contractProductHelper = $this->listsFrontendProductHelper;
        /* @var $contractProductHelper Epicor_Lists_Helper_Frontend_Product */
        $page_params = $this->getRequest()->getParam('sku');
        $this->_enteredSku = $this->getRequest()->getParam('sku');
        $this->_pageData = ($this->getRequest()->getParam('qa_page_data')) ? $this->getRequest()->getParam('qa_page_data') : [] ;
        $this->_page = $this->getRequest()->getParam('qa_page', 1);

        if ($this->_pageData == null) {
            $this->_pageData[1] = array('row_count' => $this->_rowCount, 'last_child_id' => $this->_lastChildId);
        } else {
            $this->_pageData = unserialize($helper->eccDecode($this->_pageData));
        }

        $this->_rowCount = $this->_pageData[$this->_page]['row_count'];
        $this->_lastChildId = $this->_pageData[$this->_page]['last_child_id'];

        $products = $this->getSuggestData();

        $perPage = $this->scopeConfig->getValue('Epicor_Comm/quickadd/autocomplete_result_limit', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $x = (($this->_page - 1) * $perPage) + 1;
        $maxX = $x + $perPage - 1;
        $rowId = '';
        $groupSku = '';
        $groupChildData = '';

        $locHelper = $this->commLocationsHelper;
        /* @var $locHelper Epicor_Comm_Helper_Locations */

        $locEnabled = $locHelper->isLocationsEnabled();
        $skuResults = array();

        $html = '<ul>';

        $productCount = 0;
        foreach ($products as $product) {
            /* @var $product Epicor_Comm_Model_Product */
            $nameDisplay = $product->getCustomDescription() ?: $product->getName();
            $skuDisplay = $product->getSkuDisplay();
            $isConfigurator = $product->getConfigurator() ? $product->getConfigurator() : 2;
            $typeId = $product->getTypeId();
            $productCount++;
            if ($typeId == 'grouped') {
                $skuData = $product->getSkuDisplay();
                $this->registry->register('SkipEvent', true);
                $children = $product->getTypeInstance(true)->getAssociatedProducts($product);
                $this->registry->unregister('SkipEvent');

                $childArray = array();
                foreach ($children as $child) {
                    $childArray[$child->getEntityId()] = $child;
                }
                ksort($childArray);

                $contracts = '';
                $contractstring = '';


                foreach ($childArray as $child) {
                    /* @var $child Epicor_Comm_Model_Product */
                    $eccProduct = (strpos($child->getSku(), $helper->getUOMSeparator()) !== false);
                    $skuArr = explode($helper->getUOMSeparator(), $child->getSku());
                    $uom = isset($skuArr[1]) ? $skuArr[1] : $skuArr[0];
                    $uomData = $eccProduct ? $uom : '';
                    $uomDisplay = $eccProduct ? $child->getEccUom() : $child->getName();
                    $childSkuData = $eccProduct ? $skuData : $child->getSku();
                    $childSkuDisplay = $eccProduct ? $skuData : $skuData . ' - ' . $child->getSku();

                    if ($this->_lastChildId >= $child->getEntityId()) {  // don't display the last shown child or previous children and remove from array
                        unset($childArray[$child->getEntityId()]);
                        continue;
                    }

                    if ($contractHelper->contractsEnabled()) {
                        $contracts = $contractProductHelper->activeContractsForProduct($child->getId());
                        $contractString = is_array($contracts) ? base64_encode(json_encode($contracts)) : null;
                    }

                    $html .= '<li id="super_group_' . $child->getId()
                        . '" title="' . $this->escapeHtml($childSkuData)
                        . '" class="' . ($x % 2 ? 'even' : 'odd') . ($x == 0 ? ' first' : '')
                        . '" data-uom="' . $uomData
                        . '" configurator="' . $isConfigurator
                        . '" data-pack="' . $child->getEccPackSize()
                        . '" data-id="' . $child->getId()
                        . '" decimal="' . $helper->getDecimalPlaces($child)
                        . '" data-parent="' . $product->getId() . '"'
                        //. '" data-contracts="' . $contractString
                        . ($locEnabled ? ' data-locations="' . $this->getLocationsJson($child) . '"' : '') . '>'
                        . $this->escapeHtml($childSkuDisplay) . ' - ' . $this->escapeHtml($child->getName()) . ' - ' . $this->escapeHtml($child->getEccPackSize()) . '</li>';

                    $x++;

                    unset($childArray[$child->getEntityId()]);
                    $this->_lastChildId = !empty($childArray) ? $child->getEntityId() : 0; // check if any more children to display (array will not be empty)

                    if (!empty($perPage) && $x > $maxX) {
                        $groupChildData = $childSkuData . "$$$" . $childSkuDisplay . "$$$" . $child->getName() . "$$$" . $child->getEccPackSize();
                        break;
                    }
                }
            } else {
                $this->_lastChildId = 0;
                if (!$product->getParentIds() || strpos($product->getSku(), $helper->getUOMSeparator()) === false) {
                    if ($contractHelper->contractsEnabled()) {
                        $contracts = $contractProductHelper->activeContractsForProduct($product->getId());
                        $contractString = is_array($contracts) ? base64_encode(json_encode($contracts)) : null;
                    }
                    $html .= '<li id="' . $product->getId()
                        . '" title="' . $this->escapeHtml($skuDisplay)
                        . '" class="' . ($x % 2 ? 'even' : 'odd') . ($x == 0 ? ' first' : '') . '"'
                        //. '" data-contracts="' . $contractString  . '"'
                        . '" decimal="' . $helper->getDecimalPlaces($product) . '"'
                        . ($locEnabled ? ' data-locations="' . $this->getLocationsJson($product) . '"' : '')
                        . '>' . $this->escapeHtml($skuDisplay) . ' - ' . $this->escapeHtml($nameDisplay) . '</li>';
                    $x++;
                }
            }

            $this->_rowCount++;
            $rowId = $product->getId();
            if (!empty($perPage) && $x > $maxX) {
                break;
            }
        }

        if (!$this->_lastChildId) {
            if ($productCount >= count($products)) {  // if no more children check if there is another row, if so save row id
                $rowId = false;
            }
        }
        $this->_lastChildId ? $this->_rowCount-- : null;                    // if last entry displayed was child of a row stay on same row (as there might be more children), else go to next row


        $html .= '</ul>';

        $this->_pageData[$this->_page + 1] = array('row_count' => $this->_rowCount, 'last_child_id' => $this->_lastChildId);

        $html .= '<input type="hidden" id="qa_page_data" value="' . $helper->eccEncode(serialize($this->_pageData)) . '" />';

        if ($rowId) {
            $html .= '<button type="button" class="button qa_more" id="qa_next_btn" value="' . ($this->_page + 1) . '"><span>' . __('Get More Results') . '</span></button>';
        }
        return $html;
    }

    /**
     * Applies lists filtering if lists is enabled
     * 
     * @param \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $collection
     * 
     * @return void
     */
    private function applyListFilter($collection)
    {
        if (
            $this->listsFrontendProductHelper->listsDisabled() ||
            $collection->getFlag('no_product_filtering') ||
            $collection instanceof \Magento\Bundle\Model\ResourceModel\Selection\Collection ||
            $collection->getFlag('lists_sql_applied')
        ) {
            return $collection;
        }

        if ($this->listsFrontendProductHelper->hasFilterableLists() || $this->listsFrontendContractHelper->mustFilterByContract()) {
            $productIds = $this->listsFrontendProductHelper->getActiveListsProductIds();
            $collection->addFieldToFilter('entity_id', ['in' => explode(',',$productIds)]);
            // The below is needed for filtering to work on search page (doesnt seem to conflict with above!)
            $collection->getSelect()->where(
                '(e.entity_id IN(' . $productIds . '))'
            );
            $collection->setFlag('lists_sql_applied', true);
        }
        return $collection;
    }
    
    public function getSuggestData()
    {
        if (!$this->_suggestData) {
            $commHelper = $this->commHelper;
            $locHelper = $this->commLocationsHelper;
            $connection = $this->resourceConnection->getConnection('core_read');

            $erpAccount = $commHelper->getErpAccountInfo();
            $customerGroupId = ($erpAccount && !$erpAccount->isDefaultForStore()) ? $erpAccount->getId() : 0;
            $globalGroupId = ($erpAccount && !$erpAccount->isDefaultForStore()) ? ' OR `cpn`.`customer_group_id` = "0"' : '';

            $sku = $this->getEnteredSku();
            $products = $this->catalogResourceModelProductCollectionFactory->create();
            
            $products = $this->applyListFilter($products);
            
            $products->setStoreId($this->storeManager->getStore()->getId());
            $products->addAttributeToSelect(array('sku', 'name', 'type_id', 'ecc_decimal_places'));

            /* CPN JOIN */
            $cpnTable = $products->getTable('ecc_erp_account_sku');
            $cpnCond = '`cpn`.`product_id` = `e`.`entity_id` 
                AND (`cpn`.`customer_group_id` = "' . $customerGroupId . '"' . $globalGroupId . ')
                AND `cpn`.`sku` LIKE(' . $connection->quote($sku . '%') . ')';
            $products->getSelect()->joinLeft(array('cpn' => $cpnTable), $cpnCond, array('sku_display' => new \Zend_DB_Expr('IFNULL(cpn.sku, e.sku)'), 'custom_description' => 'description'));

            /* PRODUCT LINK */
            $linkTable = $products->getTable('catalog_product_link');
            $linkCond = '`link`.`linked_product_id` = `e`.`entity_id` 
                AND `link`.`link_type_id` = "' . \Magento\GroupedProduct\Model\ResourceModel\Product\Link::LINK_TYPE_GROUPED . '"';
            $products->getSelect()->joinLeft(array('link' => $linkTable), $linkCond, array('parent_ids' => 'product_id'));

            $products->getSelect()->limit(50, $this->_rowCount);
            $products->setVisibility(array(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_CATALOG, \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH, \Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_SEARCH));
            $products->addAttributeToFilter('status', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
            $products->getSelect()->where('(`e`.`sku` like (?) OR `cpn`.`sku` LIKE(?))', $sku . '%', $sku . '%');

            
            $products->getSelect()->order(new \Zend_DB_Expr('IFNULL(cpn.sku, e.sku) asc'));
            $products->getSelect()->group('e.sku');
            $this->_suggestData = $products->getItems();
        }

        return $this->_suggestData;
    }

    public function getEnteredSku()
    {
        if (!$this->_enteredSku) {
            $this->_enteredSku = $this->request->getParam('sku');
        }

        return $this->_enteredSku;
    }

    /**
     * Processes a product and returns the locations as an encoded string
     * 
     * @param \Epicor\Comm\Model\Product $product
     * 
     * @return string
     */
    protected function getLocationsJson($product)
    {
        $locArray = $product->getCustomerLocations();
        $locData = array();
        foreach ($locArray as $locationCode => $location) {
            /* @var $location \Epicor\Comm\Model\Location\Product */
            $locData[] = array(
                'code' => $locationCode,
                'name' => $location->getName()
            );
        }
        return htmlspecialchars(json_encode($locData));
    }

}
