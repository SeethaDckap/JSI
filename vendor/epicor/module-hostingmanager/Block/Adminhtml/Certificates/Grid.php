<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\HostingManager\Block\Adminhtml\Certificates;


/**
 * Certificates grid block
 *
 * @category   Epicor
 * @package    Epicor_HostingManager
 * @author     Epicor Websales Team
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    /**
     * @var \Epicor\HostingManager\Model\ResourceModel\Certificate\CollectionFactory
     */
    protected $hostingManagerResourceCertificateCollectionFactory;


    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\HostingManager\Model\ResourceModel\Certificate\CollectionFactory $hostingManagerResourceCertificateCollectionFactory,
        array $data = []
    )
    {
        $this->hostingManagerResourceCertificateCollectionFactory = $hostingManagerResourceCertificateCollectionFactory;
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
        $collection = $this->hostingManagerResourceCertificateCollectionFactory->create();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {

        $this->addColumn('request', array(
            'header' => __('R'),
            'align' => 'center',
            'index' => 'request',
            'width' => '30px',
            'renderer' => 'Epicor\Common\Block\Adminhtml\Widget\Grid\Column\Renderer\Tickcross',
            'tick_mode' => 'content',
            'filter' => false
        ));

        $this->addColumn('private_key', array(
            'header' => __('K'),
            'align' => 'center',
            'index' => 'private_key',
            'width' => '30px',
            'renderer' => 'Epicor\Common\Block\Adminhtml\Widget\Grid\Column\Renderer\Tickcross',
            'tick_mode' => 'content',
            'filter' => false
        ));

        $this->addColumn('certificate', array(
            'header' => __('C'),
            'align' => 'center',
            'index' => 'certificate',
            'width' => '30px',
            'renderer' => 'Epicor\Common\Block\Adminhtml\Widget\Grid\Column\Renderer\Tickcross',
            'tick_mode' => 'content',
            'filter' => false
        ));

        $this->addColumn('c_a_certificate', array(
            'header' => __('A'),
            'align' => 'center',
            'index' => 'c_a_certificate',
            'width' => '30px',
            'renderer' => 'Epicor\Common\Block\Adminhtml\Widget\Grid\Column\Renderer\Tickcross',
            'tick_mode' => 'content',
            'filter' => false
        ));

        $this->addColumn('status', array(
            'header' => __('Status'),
            'align' => 'center',
            'width' => '30px',
            'cert_id' => 'entity_id',
            'renderer' => 'Epicor\HostingManager\Block\Adminhtml\Sites\Column\Renderer\Ssl',
            'filter' => false
        ));

        $this->addColumn('name', array(
            'header' => __('Name'),
            'align' => 'left',
            'index' => 'name',
        ));


        $this->addColumn('action', array(
            'header' => __('Action'),
            'width' => '100',
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
            'is_system' => true,
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

}
