<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Mapping\DataMapping;


class Grid extends \Magento\Backend\Block\Widget\Grid\Extended  
{

    /**
     * @var \Epicor\Common\Model\ResourceModel\DataMapping\CollectionFactory
     */
    protected $dataMappingFactory;

    protected $eccMappingConfig;
    
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Common\Model\ResourceModel\DataMapping\CollectionFactory $dataMappingFactory,
        \Epicor\Common\Model\EccMappingConfig $eccMappingConfig,
        array $data = [])
    {
        $this->dataMappingFactory = $dataMappingFactory;
        $this->eccMappingConfig = $eccMappingConfig;

        parent::__construct($context, $backendHelper, $data);


        $this->setId('dataMappingGrid');
        $this->setSaveParametersInSession(true);
        $jsObjectName = $this->getJsObjectName();
        $this->setAdditionalJavaScript("
        $jsObjectName.resetFilter = function(callback) {
           return false;
           };       
            require(['jquery'], function($){
             $('.action-reset').on('click',function(){
                var ajaxurl = '" . $this->getUrl('adminhtml/epicorcommon_mapping/change') ."';
                var val =  'datamapping';
                $.ajax({
                    url:ajaxurl,
                    type:'POST',
                    showLoader: true,
                    dataType:'json',
                    data: {value:val},                                      
                    success:function(response){
                        if(response.success){
                           window.location.href = response.ajaxRedirect;
                        }
                    }
                });                
             });
            });                  
        ");
    }

    protected function _prepareCollection()
    {
        $collection = $this->dataMappingFactory->create();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('message', array(
            'header' => __('Ecc Message'),
            'align' => 'left',
            'type' =>'options',
            'index' => 'message',
            'width' => '50px',
            'options' =>$this->getMessageOptions(),
        ));

        $this->addColumn('orignal_tag', array(
            'header' => __('Orignal Tag'),
            'align' => 'left',
            'index' => 'orignal_tag',

        ));

        $this->addColumn('mapped_tag', array(
            'header' => __('Mapped Tag'),
            'align' => 'left',
            'index' => 'mapped_tag',
        ));

        $params = array('base' => '*/*/edit');
        $deleteParams = array('base' => '*/*/delete');

        $this->addColumn('action', array(
            'header' => __('Action'),
            'width' => '100',
            'type' => 'action',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => __('Edit'),
                    'url' => $params,
                    'field' => 'id'
                ),
                array(
                    'caption' => __('Delete'),
                    'url' => $deleteParams,
                    'field' => 'id'
                )
            ),
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
            'is_system' => true,
        ));

//        $this->addExportType('*/*/exportCsv', __('CSV'));
//        $this->addExportType('*/*/exportXml', __('XML'));
        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        $params = array('id' => $row->getId());
        return $this->getUrl('*/*/edit', $params);
    }
    /**
     * Render country grid column
     * @return  array
     */
    public function getMessageOptions()
    {
        $request = $this->eccMappingConfig->getAttributeNames(null);
        $_upload = array(''=>'&nbsp;');
        foreach ($request as $message => $field) {
            $_upload[$message]= $field;
        }
        ksort($_upload);
        return $_upload;
    }

}