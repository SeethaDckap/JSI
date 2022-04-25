<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Block\Adminhtml\Listing\Analyse\Products;


/**
 * 
 * ERP Account grid for erp account selector input
 * 
 * @category   Epicor
 * @package    Epicor_Common
 * @author     Epicor Websales Team
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $backendAuthSession;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $catalogResourceModelProductCollectionFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $catalogResourceModelProductCollectionFactory,
        array $data = []
    )
    {
        $this->backendAuthSession = $backendAuthSession;
        $this->catalogResourceModelProductCollectionFactory = $catalogResourceModelProductCollectionFactory;
        parent::__construct(
            $context,
            $backendHelper,
            $data
        );
        $this->setId('products_grid');
        $this->setDefaultSort('name');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $data = $this->backendAuthSession->getAnalyseProductsData();
        $collection = $this->catalogResourceModelProductCollectionFactory->create();
        /* @var $collection Mage_Catalog_Model_Resource_Product_Collection */

        if ($data && is_array($data) && isset($data['type']) && isset($data['products'])) {
            if ($data['type'] == 'E') {
                $collection->addAttributeToFilter('sku', array('nin' => $data['products']));
            } else {
                $collection->addAttributeToFilter('sku', array('in' => $data['products']));
            }
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        parent::_prepareColumns();

        $this->addColumn('sku', array(
            'header' => __('Product SKU'),
            'index' => 'sku',
        ));

        $this->addColumn('name', array(
            'header' => __('Name'),
            'index' => 'name',
        ));
        return $this;
    }

    public function getRowUrl($row)
    {
        return $row->getId();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/*', array('grid' => true, 'list_id' => $this->getRequest()->getParam('list_id')));
    }

}
