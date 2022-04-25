<?php
/**
 * Copyright © 2010-2019 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Mapping\Claimstatus\Edit;


class Form extends \Epicor\Common\Block\Adminhtml\Mapping\DefaultBlock\Form
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Comm\Model\Erp\Mapping\Claimstatus
     */
    protected $commErpMappingClaimstatus;

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $formFactory;


    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Epicor\Comm\Model\Erp\Mapping\Claimstatus $commErpMappingClaimstatus,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = [])
    {
        $this->formFactory = $formFactory;
        $this->registry = $registry;
        $this->commErpMappingClaimstatus= $commErpMappingClaimstatus;
        parent::__construct($context, $data);
    }
    protected function _prepareForm()
    {
        if ($this->_session->getClaimStatusMappingData()) {
            $data = $this->_session->getClaimStatusMappingData();
            $this->_session->getClaimStatusMappingData(null);
        } elseif ($this->registry->registry('claimstatus_mapping_data')) {
            $data = $this->registry->registry('claimstatus_mapping_data')->getData();
        } else {
            $data = array();
        }
        $form = $this->formFactory->create(
            ['data' => [
                'id' => 'edit_form',
                'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
                'method' => 'post',
                'enctype' => 'multipart/form-data']
            ]
        );
        $form->setUseContainer(true);

        $this->setForm($form);

        $fieldset = $form->addFieldset('mapping_form', array(
            'legend' => __('Mapping Information')
        ));
        $fieldset->addField('erp_code', 'text', array(
            'label' => __('ERP Code'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'erp_code',
            'note' => __('ERP Code'),
        ));
        
        $eccClaimStatus = $this->commErpMappingClaimstatus->getEccClaimStatus();
        array_unshift($eccClaimStatus, array('value' => '', 'label' => ''));
        $fieldset->addField('claim_status', 'select', array(
            'name' => 'claim_status',
            'label' => __('ECC Status'),
            'class' => 'required-entry',
            'required' => true,
            'values' => $eccClaimStatus,
            'width'=>'200px',
                )
        );


        $data = $this->includeStoreIdElement($data);
        $form->setValues($data);

        return parent::_prepareForm();
    }

}
