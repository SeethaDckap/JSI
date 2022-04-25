<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Mapping\Orderstatus\Edit;


class Form extends \Epicor\Common\Block\Adminhtml\Mapping\DefaultBlock\Form
{


    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory
     */
    protected $salesResourceModelOrderStatusCollectionFactory;

    /**
     * @var \Magento\Sales\Model\Order\Config
     */
    protected $salesOrderConfig;

    /**
     * @var \Epicor\Comm\Model\Config\Source\SoutriggerFactory
     */
    protected $commConfigSourceSoutriggerFactory;

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $formFactory;
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory $salesResourceModelOrderStatusCollectionFactory,
        \Magento\Sales\Model\Order\Config $salesOrderConfig,
        \Epicor\Comm\Model\Config\Source\SoutriggerFactory $commConfigSourceSoutriggerFactory,
        array $data = [])
    {

        $this->registry = $registry;
        $this->formFactory = $formFactory;
        $this->salesResourceModelOrderStatusCollectionFactory = $salesResourceModelOrderStatusCollectionFactory;
        $this->salesOrderConfig = $salesOrderConfig;
        $this->commConfigSourceSoutriggerFactory = $commConfigSourceSoutriggerFactory;
        parent::__construct($context, $data);
    }
    protected function _prepareForm()
    {
        if ($this->_session->getOrderstatusMappingData()) {
            $data = $this->_session->getOrderstatusMappingData();
            $this->_session->getOrderstatusMappingData(null);
        } elseif ($this->registry->registry('orderstatus_mapping_data')) {
            $data = $this->registry->registry('orderstatus_mapping_data')->getData();
        } else {
            $data = array();
        }

        $form = $this->formFactory->create( ['data' => [
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
            'method' => 'post',
            'enctype' => 'multipart/form-data']
        ]);

        $form->setUseContainer(true);

        $this->setForm($form);

        $fieldset = $form->addFieldset('mapping_form', array(
            'legend' => __('Mapping Information')
        ));

        $fieldset->addField('code', 'text', array(
            'label' => __('Code'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'code',
            'note' => __('Order Status Code'),
        ));


        $statuses = $this->salesResourceModelOrderStatusCollectionFactory->create()
            ->toOptionArray();
        array_unshift($statuses, array('value' => '', 'label' => ''));

        $states = array_merge(array('' => ''), $this->salesOrderConfig->getStates());
        $fieldset->addField('status', 'select', array(
            'name' => 'status',
            'label' => __('Order Status'),
            'class' => 'required-entry',
            'values' => $statuses,
            'required' => true,
            )
        );

//        $fieldset->addField('state', 'select',
//            array(
//                'name'      => 'state',
//                'label'     => Mage::helper('sales')->__('Order State'),
//                'class'     => 'required-entry',
//                'values'    => $states,
//                'required'  => true,
//            )
//        );

        $fieldset->addField('sou_trigger', 'select', array(
            'name' => 'sou_trigger',
            'label' => __('Sou Trigger'),
            'values' => $this->commConfigSourceSoutriggerFactory->create()->toOptionArray(),
            )
        );

        $data = $this->includeStoreIdElement($data);

        $form->setValues($data);

        return parent::_prepareForm();
    }

}
