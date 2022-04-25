<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Mapping\Customfields;


class Grid extends \Magento\Backend\Block\Widget\Grid\Extended  
{

    /**
     * @var \Epicor\Dealerconnect\Model\ResourceModel\EccPacAttributes\CollectionFactory
     */
    protected $customfieldsFactory;

    protected $globalConfig;
    
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Supplierconnect\Model\ResourceModel\Customfields\CollectionFactory $customfieldsFactory,
        \Epicor\Comm\Model\GlobalConfig\Config $globalConfig,
        array $data = [])
    {
        $this->customfieldsFactory = $customfieldsFactory;
        $this->globalConfig = $globalConfig;
        parent::__construct($context, $backendHelper, $data);
        $this->setId('customfieldsGrid');
        $this->setSaveParametersInSession(true);
        $jsObjectName = $this->getJsObjectName();
        $this->setAdditionalJavaScript("
        $jsObjectName.resetFilter = function(callback) {
           return false;
           };       
            require(['jquery'], function($){
             $('.action-reset').on('click',function(){
                var ajaxurl = '" . $this->getUrl('adminhtml/epicorcommon_mapping/change') ."';
                var val =  'customfields';
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
        $collection = $this->customfieldsFactory->create();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $preselected = $this->_request->getParam('preselectedFields');
        $msec = $this->_request->getParam('msec');

        $this->addColumn('message', array(
            'header' => __('Ecc Message'),
            'align' => 'left',
            'type' =>'options',
            'index' => 'message',
            'width' => '50px',
            'options' =>$this->getMessageOptions(),
        ));

        $this->addColumn('message_section', array(
            'header' => __('Message Section'),
            'align' => 'left',
            'type' =>'options',
            'index' => 'message_section',
            'options' =>$this->getMessageSectionOptions()
        ));

        $this->addColumn('custom_fields', array(
            'header' => __('Custom Fields'),
            'align' => 'left',
            'index' => 'custom_fields',
        ));

        $params = array('base' => '*/*/edit');
        $deleteParams = array('base' => '*/*/delete');
        if($preselected) {
            $params = array('base' => '*/*/edit/preselectedFields/'.$preselected.'/msec/'.$msec);
            $deleteParams = array('base' => '*/*/delete/preselectedFields/'.$preselected.'/msec/'.$msec);
        }

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

        $this->addExportType('*/*/exportCsv', __('CSV'));
        $this->addExportType('*/*/exportXml', __('XML'));


        if($preselected) {
            $this->setDefaultFilter(array('message'=>$preselected,'message_section'=>$msec));
        }

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        $preselected = $this->_request->getParam('preselectedFields');
        $msec = $this->_request->getParam('msec');
        $params = array('id' => $row->getId());
        if($preselected) {
            $params = array('id' => $row->getId(),'preselectedFields'=>$preselected,'msec'=>$msec);
        }
        return $this->getUrl('*/*/edit', $params);
    }
    
    public function getStatusName()
    {
        $statusName = array('yes' => 'Active', 'no' => 'Inactive');
        return $statusName;
    }

    /**
     * Render country grid column
     *
     * @param   \Epicor\Comm\Model\Location\Product $row
     *
     * @return  string
     */
    public function getMessageOptions()
    {
        $request = (array) $this->globalConfig->get('mapping_xml_request/messages');
        //M1 > M2 Translation End
        $_upload = array(''=>'&nbsp;');
        foreach ($request as $key => $request) {
            $_upload[strtoupper($key)] = $request['title'];
        }
        return $_upload;
    }

    /**
     * Render country grid column
     *
     * @param   \Epicor\Comm\Model\Location\Product $row
     *
     * @return  string
     */
    public function getMessageSectionOptions()
    {
        $request = (array) $this->globalConfig->get('mapping_xml_request/message_sections');
        //M1 > M2 Translation End
        $_upload = array();
        foreach ($request as $key => $request) {
            $_upload[$key] = $request;
        }
        return $_upload;

    }
    
}