<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Mapping\Customfields;


class Edit extends  \Epicor\Common\Block\Adminhtml\Mapping\DefaultBlock\Edit
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
        \Magento\Backend\Block\Widget\Context  $context,
        \Magento\Framework\Registry $registry,
        array $data)
    {
        $this->registry = $registry;
        parent::__construct($context, $data);

        $this->_objectId = 'id';
        $this->_controller = 'adminhtml\Mapping_Customfields';
        $this->_blockGroup = 'Epicor_Common';
        $this->_mode = 'edit';

        $this->addButton('save_and_continue', array(
            'label' => __('Save And Continue Edit'),
            'onclick' => 'saveAndContinueEdit()',
            'class' => 'save',
            ), -100);
        $this->updateButton('save', 'label', __('Save'));
        $preselecteds = $this->_request->getParam('preselectedFields');
        $msec = $this->_request->getParam('msec');
        $urlKey = '';
        if($preselecteds) {
            $urlKey='?preselectedFields='.$preselecteds.'&msec='.$msec;
        }
        $this->_formScripts[] = "
           require(['jquery'], function($){
                var saveUrl = $('#edit_form').attr('action');
                $('#save_and_continue').on('click',function(){
                    $('#edit_form').attr('action',saveUrl + 'back/edit/$urlKey')
                    $('#edit_form').eq(0).submit();
                });
           });
        ";        
    }

    public function getBackUrl()
    {
        return $this->getUrl('*/*/', $this->_getStoreParams());
    }

    public function getDeleteUrl()
    {
        $preselecteds = $this->_request->getParam('preselectedFields');
        $msec = $this->_request->getParam('msec');
        $params = [$this->_objectId => $this->getRequest()->getParam($this->_objectId)];
        if($preselecteds) {
            $params = [$this->_objectId => $this->getRequest()->getParam($this->_objectId),'preselectedFields'=>$preselecteds,'msec'=>$msec];
        }
        return $this->getUrl('*/*/delete', $params);
    }

    private function _getStoreParams()
    {
        $storeId = (int) $this->getRequest()->getParam('store');
        $preselecteds = $this->_request->getParam('preselectedFields');
        $msec = $this->_request->getParam('msec');
        $params = array();
        if($preselecteds && is_null($storeId)) {
            $params = array('preselectedFields'=>$preselecteds,'msec'=>$msec);
        }
        $storeparams = array('store'=>$storeId);
        if($preselecteds && !is_null($storeId)) {
            $storeparams = array('preselectedFields'=>$preselecteds,'store' => $storeId,'msec'=>$msec);
        }

        return is_null($storeId) ? $params : $storeparams;
    }

    public function getHeaderText()
    {
        return __('View Customfields Mapping');
    }
}
