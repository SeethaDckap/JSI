<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Advanced\Postdata\Upload;


/**
 * 
 * Form for post Data
 * 
 * @category   Epicor
 * @package    Epicor_Common
 * @author     Epicor Websales Team
 */
class Form extends \Magento\Backend\Block\Widget\Form
{

    /**
     * @var \Epicor\Common\Helper\Data
     */
    protected $commonHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $formFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        $this->formFactory = $formFactory;
        $this->commonHelper = $commonHelper;
        $this->registry = $registry;
        parent::__construct(
            $context,
            $data
        );
    }


    protected function _prepareForm()
    {

        $form = $this->formFactory->create([
            'data' => [
                'id' => 'edit_form',
                'action' => $this->getUrl('*/*/postdata'),
                'method' => 'post'
            ]
        ]);

        $form->setUseContainer(true);
        $this->setForm($form);

        $fieldset = $form->addFieldset('layout_block_form', array('legend' => __('Post Data')));

        $fieldset->addField('post_data_store_id', 'select', array(
            'name' => 'post_data_store_id',
            'label' => __('Store(s)'),
            'title' => __('Store(s)'),
            'class' => 'validate-select',
            'required' => true,
            'values' => $this->commonHelper->getAllStoresFormatted(),
        ));


        $fieldset->addField(
            'xml', 'textarea', array(
            'label' => __('XML Message'),
            'required' => true,
            'name' => 'xml',
            'style' => 'height:500px'
            )
        );
        
        $fieldset->addField(
            'post-xml', 'hidden', array(
            'label' => __('XML Message'),
            'required' => true,
            'name' => 'post-xml'            )
        );
        
        if ($this->registry->registry('ECC_Message_Response')) {
            $fieldset->addField(
                'erp_response',
                'textarea',
                [
                    'label' => __('ERP Response'),
                    'readonly' => true,
                    'name' => 'erp_response',
                    'style' => 'height:500px'
                ]
            );
        }

        $form->setValues($this->registry->registry('posted_xml_data'));
        return parent::_prepareForm();
    }

}
