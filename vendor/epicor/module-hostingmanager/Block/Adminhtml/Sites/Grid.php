<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\HostingManager\Block\Adminhtml\Sites;


/**
 * Hosting Sites Grid
 *
 * @category   Epicor
 * @package    Epicor_HostingManager
 * @author     Epicor Websales Team
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    /**
     * @var \Epicor\HostingManager\Model\ResourceModel\Site\CollectionFactory
     */
    protected $hostingManagerResourceSiteCollectionFactory;


    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\HostingManager\Model\ResourceModel\Site\CollectionFactory $hostingManagerResourceSiteCollectionFactory,
        array $data = []
    )
    {
        $this->hostingManagerResourceSiteCollectionFactory = $hostingManagerResourceSiteCollectionFactory;
        $this->setId('entity_id');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('ASC');
        parent::__construct(
            $context,
            $backendHelper,
            $data
        );
    }

    protected function _prepareCollection()
    {
        $collection = $this->hostingManagerResourceSiteCollectionFactory->create();
        /* @var $collection \Epicor\HostingManager\Model\ResourceModel\Site\Collection */

        $cert_table = $collection->getTable('ecc_hosting_certificate');

        $collection->getSelect()->joinLeft(
            array('cert' => $cert_table), 'certificate_id=cert.entity_id', array(
            'cert_name' => 'name',
        ));
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Sets up the stores for each row so we can differentiate stores display
     * between is_website y/n sites
     */
    protected function _afterLoadCollection()
    {
        parent::_afterLoadCollection();

        $items = $this->getCollection()->getItems();

        //M1 > M2 Translation Begin (Rule p2-6.5)
        //$stores = array(Mage::app()->getDefaultStoreView()->getId());
        $stores = array($this->_storeManager->getDefaultStoreView()->getId());
        //M1 > M2 Translation End

        foreach ($items as $item) {
            if (!$item->getIsWebsite()) {
                $stores[] = $item->getChildId();
            }
        }

        foreach ($items as $item) {
            $item->setIgnoreStores($stores);
        }
    }

    protected function _prepareColumns()
    {

        $this->addColumn('name', array(
            'header' => __('Name'),
            'align' => 'left',
            'index' => 'name',
        ));

        $this->addColumn('url', array(
            'header' => __('Url'),
            'align' => 'left',
            'index' => 'url',
        ));

        $this->addColumn('stores', array(
            'header' => __('Stores'),
            'align' => 'left',
            'index' => 'child_id',
            'renderer' => 'Epicor\HostingManager\Block\Adminhtml\Sites\Column\Renderer\Stores',
        ));

        $this->addColumn('cert_name', array(
            'header' => __('SSL Status'),
            'align' => 'left',
            'index' => 'cert_name',
            'cert_id' => 'certificate_id',
            'renderer' => 'Epicor\HostingManager\Block\Adminhtml\Sites\Column\Renderer\Ssl',
            'show_name' => true,
            'filter_index' => 'cert.name'
        ));

        $this->addColumn('edit', array(
            'header' => __(''),
            'width' => '50',
            'type' => 'action',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => __('Edit'),
                    'url' => array('base' => '*/*/edit'),
                    'field' => 'id'
                ),
            ),
            'filter' => false,
            'sortable' => false,
            'index' => 'id',
            'is_system' => true
        ));
        $this->addColumn('delete', array(
            'header' => __(''),
            'width' => '50',
            'type' => 'action',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => __('Delete'),
                    'url' => array('base' => '*/*/delete'),
                    'field' => 'id',
                    'onclick' => 'return confirm(' . __("Are you sure you want to do this?") . ');'
                ),
            ),
            'filter' => false,
            'sortable' => false,
            'index' => 'id',
            'is_system' => true
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

}
