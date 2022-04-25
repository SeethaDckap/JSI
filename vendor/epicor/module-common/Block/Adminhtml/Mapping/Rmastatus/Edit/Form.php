<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Mapping\Rmastatus\Edit;


class Form extends \Epicor\Common\Block\Adminhtml\Mapping\DefaultBlock\Form
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $cmsWysiwygConfig;

    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $backendUrlInterface;

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $formFactory;
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Cms\Model\Wysiwyg\Config $cmsWysiwygConfig,
        \Magento\Backend\Model\UrlInterface $backendUrlInterface,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = [])
    {
        $this->formFactory = $formFactory;
        $this->registry = $registry;
        $this->cmsWysiwygConfig = $cmsWysiwygConfig;
        $this->backendUrlInterface = $backendUrlInterface;
        parent::__construct($context, $data);
    }
    protected function _prepareForm()
    {
        if ($this->_session->getRmastatusMappingData()) {
            $data = $this->_session->getRmastatusMappingData();
            $this->_session->getRmastatusMappingData(null);
        } elseif ($this->registry->registry('rmastatus_mapping_data')) {
            $data = $this->registry->registry('rmastatus_mapping_data')->getData();
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

        $fieldset = $form->addFieldset(
            'mapping_form', array(
            'legend' => __('Mapping Information')
            )
        );

        $fieldset->addField(
            'code', 'text', array(
            'label' => __('Code'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'code',
            'note' => __('Rma Status Code'),
            )
        );


        $fieldset->addField(
            'status', 'text', array(
            'name' => 'status',
            'label' => __('Rma Status'),
            'class' => 'required-entry',
            'values' => 'status',
            'required' => true,
            )
        );

       /* $wysiwygConfig = $this->cmsWysiwygConfig->getConfig();
        $wysiwygConfig->addData(
            array(
                'variables_wysiwyg_action_url' => $this->backendUrlInterface->getUrl('adminhtml/system_variable/wysiwygPlugin'),
                'widget_window_url' => $this->backendUrlInterface->getUrl('adminhtml/widget/index'),
                'directives_url' => $this->backendUrlInterface->getUrl('adminhtml/cms_wysiwyg/directive'),
                'directives_url_quoted' => preg_quote($this->backendUrlInterface->getUrl('adminhtml/cms_wysiwyg/directive')),
                'files_browser_window_url' => $this->backendUrlInterface->getUrl('adminhtml/cms_wysiwyg_images/index'),
            )
        );*/

        $fieldset->addField(
            'status_text', 'editor', array(
            'label' => __('Status Text Displayed to Customers'),
            'name' => 'status_text',
            'config' => $this->cmsWysiwygConfig->getConfig(),
            'wysiwyg' => true,
            'style' => 'height:12em;width:500px;',
            )
        );

        $fieldset->addField(
            'is_rma_deleted', 'checkbox', array(
            'name' => 'is_rma_deleted',
            'label' => __('Delete RMAs Changed to this Status?'),
            'checked' =>(isset($data['is_rma_deleted']) && $data['is_rma_deleted']) ? 'checked' : ''
            )
        );


//
//        $fieldset->addField('state', 'text',
//            array(
//                'name'      => 'state',
//                'label'     => Mage::helper('sales')->__('Rma State'),
//                'class'     => 'required-entry',
//                'values'    => 'state',
//                'required'  => true,
//            )
//        );

        $data = $this->includeStoreIdElement($data);

        $form->setValues($data);

        return parent::_prepareForm();
    }

}
