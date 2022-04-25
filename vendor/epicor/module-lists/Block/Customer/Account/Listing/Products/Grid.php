<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Block\Customer\Account\Listing\Products;


use Epicor\Lists\Block\Adminhtml\Listing\Edit\Tab\Renderer\SkunodelimiterFactory;
use Epicor\Lists\Helper\Data as ListsHelper;
use Epicor\Lists\Helper\Frontend\Contract;
use Magento\Catalog\Model\Product;
use Epicor\Lists\Helper\Frontend\Product as ProductHelper;
use Epicor\Lists\Model\ListModel\TypeFactory;
use Epicor\Lists\Model\ListModelFactory;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Epicor\Lists\Model\ResourceModel\Product\CollectionFactory;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Model\Entity;
use Magento\Framework\Registry;
use Magento\Sitemap\Model\ResourceModel\Catalog\Product as ProductResource;

/**
 * List's  product Grid config
 * 
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    const ECC_UOM_ATTRIBUTE_CODE = 'ecc_uom';

    private $selected = [];

    /**
     * @var Product
     */
    protected $listsFrontendProductHelper;

    /**
     * @var Contract
     */
    protected $listsFrontendContractHelper;

    /**
     * @var CollectionFactory
     */
    protected $catalogResourceModelProductCollectionFactory;

    /**
     * @var Data
     */
    protected $listsHelper;

    /**
     * @var TypeFactory
     */
    protected $listsListModelTypeFactory;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var ListModelFactory
     */
    protected $listsListModelFactory;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $generic;

    /**
     * @var SkunodelimiterFactory
     */
    protected $listsAdminhtmlListingEditTabRendererSkunodelimiterFactory;

     // protected $_template = 'Epicor_Common::widget/grid/container.phtml';
     protected $_template = 'Epicor_Common::widget/grid/extended.phtml';
    /**
     * @var Product
     */
    private $productResource;
    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;
    /**
     * @var Entity
     */
    private $eavEntity;
    /**
     * @var \Epicor\Comm\Helper\Locations
     */
    protected $commLocationsHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Epicor\Lists\Model\ResourceModel\NewListgrid
     */
    protected $listsTmp;

    public function __construct(
        Context $context,
        Data $backendHelper,
        ProductHelper $listsFrontendProductHelper,
        Contract $listsFrontendContractHelper,
        CollectionFactory $catalogResourceModelProductCollectionFactory,
        \Epicor\Lists\Model\ResourceModel\NewListgrid $listsTmp,
        ListsHelper $listsHelper,
        TypeFactory $listsListModelTypeFactory,
        Registry $registry,
        ListModelFactory $listsListModelFactory,
        ProductResource $productResource,
        Entity $eavEntity,
        AttributeRepositoryInterface $attributeRepository,
        SkunodelimiterFactory $listsAdminhtmlListingEditTabRendererSkunodelimiterFactory,
        \Epicor\Comm\Helper\Locations $commLocationsHelper,
        array $data = []
    ) {
        $this->listsAdminhtmlListingEditTabRendererSkunodelimiterFactory
            = $listsAdminhtmlListingEditTabRendererSkunodelimiterFactory;
        $this->listsFrontendProductHelper = $listsFrontendProductHelper;
        $this->listsFrontendContractHelper = $listsFrontendContractHelper;
        $this->catalogResourceModelProductCollectionFactory = $catalogResourceModelProductCollectionFactory;
        $this->listsHelper = $listsHelper;
        $this->listsListModelTypeFactory = $listsListModelTypeFactory;
        $this->registry = $registry;
        $this->listsListModelFactory = $listsListModelFactory;
        $this->generic = $context->getSession();
        $this->commLocationsHelper = $commLocationsHelper;
        $this->customerSession = $commLocationsHelper->customerSessionFactory();
        $this->listsTmp = $listsTmp;
        parent::__construct(
            $context,
            $backendHelper,
            $data
        );
        $this->setId('list_products');
       // $this->setIdColumn('id');
        $this->setSaveParametersInSession(false);
        $this->setUseAjax(true);
        $this->setCacheDisabled(true);
        $this->setFilterVisibility(true);
        $this->setDefaultFilter(array(
            'selected_products' => 1
        ));
        $this->attributeRepository = $attributeRepository;
        $this->eavEntity = $eavEntity;
        $this->productResource = $productResource;
    }

    protected function _prepareCollection()
    {
        $helper = $this->listsFrontendProductHelper;
        /* @var $helper Epicor_Lists_Helper_Frontend_Product */
        $contractHelper = $this->listsFrontendContractHelper;
        /* @var $contractHelper Epicor_Lists_Helper_Frontend_Contract */
        $collection = $this->catalogResourceModelProductCollectionFactory->create();
        $collection->addAttributeToSelect('name');
        $collection->addAttributeToSelect('ecc_uom');
        $collection->addAttributeToSelect('sku');
        $collection->addAttributeToSelect('qty');
        //prevent the observer event
        $collection->setFlag('no_location_filtering', true);
        $collection->setFlag('no_product_filtering', true);

        if ($helper->hasFilterableLists() || $contractHelper->mustFilterByContract()) {
            $productIds = $helper->getActiveListsProductIds();
            $skus = $this->_getSelected();
            $collection->getSelect()->where(
                '(e.entity_id IN(' . $productIds . ') OR e.sku IN ("' . join('","', $skus) . '"))'
            );
        }

//        $collection->addFieldToFilter('type_id', array(
//            'neq' => 'grouped'
//        ));
        //allow duplication of product entity ids
        $collection->setFlag('allow_duplicate', 1);
        //retrieve product qty from lists product table
        $productinfo = $this->customerSession->getProductInfo();
        $product_info = json_decode(base64_decode(strtr($productinfo, '-_', '+/')), true);
        $productsArray = [];
        $params = $this->getRequest()->getParams();
        if($product_info) {
            foreach ($product_info as $key => $val) {
                if (is_array($val)) {
                    $productsArray[] = ['sku' => $val['sku'], 'qty' => $val['qty'],'location_code' => $val['location_code']];
                } else {
                    $productsArray[] = ['sku' => $val['sku'], 'qty' => $val, 'location_code' => $val['location_code']];
                }
            }

            $collection = $this->listsTmp->applyListFilter($collection,$productsArray);
        }else {
            $collection->getSelect()->joinLeft(
                array('lp' => $collection->getTable('ecc_list_product')),
                'e.sku = lp.sku AND lp.list_id = "' . $this->getList()->getId() . '"', array('qty', 'location_code')
            );
        }
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return int|string|null
     */
    private function getUomAttributeId()
    {
        $id = '';
        try {
            $id = $this->attributeRepository
                ->get($this->getProductEntityTypeId(), $this::ECC_UOM_ATTRIBUTE_CODE)
                ->getAttributeId();
        } catch (\Exception $e) {
            $this->_logger->error($e->getTraceAsString());
        }
        return $id;
    }

    private function getProductEntityTypeId()
    {
        return $this->eavEntity->setType(Product::ENTITY)->getTypeId();
    }

    protected function _mandatoryProducts($collection)
    {
        $listSettings = $this->getList()->getSettings();
        $restrict = false;
        if (in_array('M', $listSettings)) {
            $restrict = true;
            $ids = array(
                0
            );
        }
        if ($restrict) {
            $ids = $this->_getSelected();
            if (!empty($ids)) {
                $collection->addFieldToFilter('sku', array(
                    'in' => $ids
                ));
            } else {
                $collection->addFieldToFilter('sku', array(
                    'in' => $ids
                ));
            }
            return $collection;
        }
    }

    protected function _toHtml()
    {
        $html = parent::_toHtml(false);
        return $html;
    }

    public function getRowUrl($row)
    {
        return '#';
    }

    protected function _prepareColumns()
    {
        $helper = $this->listsHelper;
        $typeModel = $this->listsListModelTypeFactory->create();

        $this->addColumn('selected_products', array(
            'header' => __('Select'),
            'index' => 'sku',
            'header_css_class' => 'a-center',
            'type' => 'checkbox',
            'name' => 'selected_products',
            'values' => $this->_getSelected(),
            'align' => 'center',
            'filter_index' => 'main_table.sku',
            'sortable' => false,
            'field_name' => 'links[]',
            'use_index' => true
        ));

        $this->addColumn('sku', array(
            'header' => __('Sku'),
            'index' => 'sku',
            'type' => 'text',
            'renderer' => '\Epicor\Lists\Block\Adminhtml\Listing\Edit\Tab\Renderer\Skunodelimiter'
        ));


        $this->addColumn(
            'uom', array(
                'header' => __('UOM'),
                'index' => 'ecc_uom',
                'filter_index' => 'ecc_uom',
                'type' => 'text',
                'sortable' => true,
                'filterable' => true,
            )
        );

        $this->addColumn('qty', array(
            'header' => __('Qty'),
            'index' => 'qty',
            'type' => 'input',
            'validate_class' => 'validate-number',
            'renderer' => '\Epicor\Lists\Block\Customer\Account\Listing\Renderer\Qty'
        ));

        if($this->commLocationsHelper->isLocationsEnabled()) {
            $this->addColumn('loc_display', array(
                'header' => __('Location'),
                'index' => 'location_code',
                'type' => 'text',
                'renderer' => '\Epicor\Lists\Block\Customer\Account\Listing\Renderer\Location'
            ));
        }else{
            $this->addColumn('loc_display', array(
                'header' => __('Location'),
                'index' => 'location_code',
                'type' => 'text',
                'renderer' => '\Epicor\Lists\Block\Customer\Account\Listing\Renderer\Location',
                'column_css_class' => "no-display",
                'header_css_class' => "no-display"
            ));
        }

        $this->addColumn('loc', array(
            'header' => __('Location'),
            'index' => 'location_code',
            'type' => 'text',
            'column_css_class' => "no-display",
            'header_css_class' => "no-display"
        ));

        $this->addColumn('name', array(
            'header' => __('Name'),
            'index' => 'name',
            'type' => 'text'
        ));

        $this->addColumn('imgicon', array(
            'header' => __(''),
            'type' => 'text',
            'sortable' => false,
            'filterable' => false,
            'renderer' => '\Epicor\Lists\Block\Customer\Account\Listing\Renderer\IsGrouped',
        ));

        $this->addColumn('row_id', array(
            'header' => __('Position'),
            'name' => 'row_id',
            'type' => 'number',
            'validate_class' => 'validate-number',
            'index' => 'entity_id',
            'width' => 0,
            'editable' => true,
            'column_css_class' => 'no-display',
            'header_css_class' => 'no-display'
        ));
        return parent::_prepareColumns();
    }

    /**
     * Used in grid to return selected Products values.
     *
     * @return array
     */
    protected function _getSelected()
    {
        return array_map('strval', array_keys($this->getSelected()));
    }

    /**
     * Gets the List for this tab
     *
     * @return boolean
     */
    public function getList()
    {
        if (!isset($this->list)) {
            if ($this->registry->registry('list')) {
                $this->list = $this->registry->registry('list');
            } else {
                $this->list = $this->listsListModelFactory->create()->load($this->getRequest()->getParam('list_id'));
            }
        }
        return $this->list;
    }

    /**
     * Builds the array of selected Products
     *
     * @return array
     */
    public function getSelected()
    {
        if (!$this->getList()->getId()) {
            $selectedProducts = $this->generic->getSelectedProducts(true);
            if (is_array($selectedProducts)) {
                foreach ($selectedProducts as $product) {
                    $this->selected[$product] = array(
                        'sku' => $product
                    );
                }
            }
        }

        if (empty($this->selected) && $this->getList()->getId()) {
            $list = $this->getList();
            /* @var $list Epicor_Lists_Model_ListModel */
            foreach ($list->getProducts() as $product) {
                $this->selected[$product->getSku()] = array(
                    'sku' => $product->getSku()
                );
            }
        }
        $productinfo = $this->customerSession->getProductInfo();
        $product_info = json_decode(base64_decode(strtr($productinfo, '-_', '+/')), true);
        if($product_info) {
            foreach ($product_info as $key => $val) {
                $this->selected[$val['sku']] = array(
                    'sku' => $val['sku']
                );
            }
        }
        return $this->selected;
    }

    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in product flag
        if ($column->getId() == 'selected_products') {
            $skus = $this->_getSelected();

            if (($column->getFilter()->getValue()) ) {
                $this->getCollection()->addFieldToFilter('sku', array(
                    'in' => $skus
                ));
            } else {
                if ($skus) {
                    $this->getCollection()->addFieldToFilter('sku', array(
                        'nin' => $skus
                    ));
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }

        return $this;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/productsgrid', array(
                '_current' => true
        ));
    }

}
