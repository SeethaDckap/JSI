<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Mapping\Remotelinks\Edit;


class Form extends \Epicor\Common\Block\Adminhtml\Mapping\DefaultBlock\Form
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Comm\Model\Config\Source\Remotelinkobjects
     */
    protected $commConfigSourceRemotelinkobjects;

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
        \Epicor\Comm\Model\Config\Source\Remotelinkobjects $commConfigSourceRemotelinkobjects,
        \Magento\Cms\Model\Wysiwyg\Config $cmsWysiwygConfig,
        \Magento\Backend\Model\UrlInterface $backendUrlInterface,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = [])
    {
        $this->formFactory = $formFactory;
        $this->registry = $registry;
        $this->commConfigSourceRemotelinkobjects = $commConfigSourceRemotelinkobjects;
        $this->cmsWysiwygConfig = $cmsWysiwygConfig;
        $this->backendUrlInterface = $backendUrlInterface;
        parent::__construct($context, $data);
    }
    protected function _prepareForm()
    {
        if ($this->_session->getRemotelinksMappingData()) {
            $data = $this->_session->getRemotelinksMappingData();
            $this->_session->getRemotelinksMappingData(null);
        } elseif ($this->registry->registry('remotelinks_mapping_data')) {
            $data = $this->registry->registry('remotelinks_mapping_data')->getData();
            if (array_key_exists('name', $data)) {
                $this->_session->setRemoteLink($data['name']);
            } else {
                $this->_session->unsRemoteLink();
            }
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

        $fieldset->addField('pattern_code', 'text', array(
            'label' => __('Pattern Code'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'pattern_code',
        ));

        $fieldset->addField('name', 'select', array(
            'label' => __('Name'),
            'options' => $this->commConfigSourceRemotelinkobjects->toOptionArray(),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'name',
        ));

        // set up config for editor, so that urls work
        $wysiwygConfig = $this->cmsWysiwygConfig->getConfig();

        //as setting variables_wysiwyg_action_url doesn't work, have to update plugins element
        $plugins = $wysiwygConfig->getData('plugins');
        $plugins[0]['options']['url'] = $this->backendUrlInterface->getUrl('adminhtml/epicorcomm_system_variable/wysiwygPlugin');
        $plugins[0]["options"]["onclick"]["subject"] = "MagentovariablePlugin.loadChooser('" . $this->backendUrlInterface->getUrl('adminhtml/epicorcomm_system_variable/wysiwygPlugin') . "', '{{html_id}}');";
        $wysiwygConfig->setData('plugins', $plugins);
        $wysiwygConfig->setAddImages(false);
        $wysiwygConfig->setAddWidgets(false);

        $fieldset->addField('url_pattern', 'editor', array(
            'label' => __('Url Pattern'),
            'name' => 'url_pattern',
            'required' => true,
            'config' => $wysiwygConfig,
            'wysiwyg' => false,
            'style' => 'height:4em;width:500px;',
        ));
        $fieldset->addField('auth_user', 'text', array(
            'label' => __('Authorised User'),
            'required' => false,
            'name' => 'auth_user',
        ));
        $fieldset->addField('auth_password', 'password', array(
            'label' => __('Authorised Password'),
            'required' => false,
            'name' => 'auth_password',
        ));

        $data = $this->includeStoreIdElement($data);

        $form->setValues($data);

        return parent::_prepareForm();
    }

}
