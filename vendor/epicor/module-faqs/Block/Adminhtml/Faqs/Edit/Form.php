<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Faqs\Block\Adminhtml\Faqs\Edit;


/**
 * F.A.Q. adminhtml edit form  
 * 
 * @category   Epicor
 * @package    Faq
 * @author     Epicor Websales Team
 *
 */
class Form extends \Magento\Backend\Block\Widget\Form
{

    /**
     * @var \Epicor\Faqs\Helper\Data
     */
    protected $faqsHelper;

    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $cmsWysiwygConfig;


    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $formFactory;
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Epicor\Faqs\Helper\Data $faqsHelper,
        \Magento\Cms\Model\Wysiwyg\Config $cmsWysiwygConfig,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        $this->formFactory = $formFactory;
        $this->faqsHelper = $faqsHelper;
        $this->cmsWysiwygConfig = $cmsWysiwygConfig;
        $this->eventManager = $context->getEventManager();
        $this->registry=$registry;
        parent::__construct(
            $context,
            $data
        );
    }


    /**
     * Prepare form action
     *
     * @return \Epicor\Faqs\Block\Adminhtml\Faqs\Edit\Form
     */
    protected function _prepareForm()
    {
        /* @var $model Epicor_Faqs_Model_Faqs */
        $data = $this->registry->registry('epicor_faq_data');

        if(isset($data['stores']) && $data['stores'] != "") {
            $form_storedata = $data['stores'];
            if (strpos($form_storedata, ',') !== false) {
                $data['stores'] = explode(',', $form_storedata);
            }
        }
        //Create form object
        $form = $this->formFactory->create( [
            'data' => [
                'id' => 'edit_form',
                'action' => $this->getUrl('*/*/save', ['id' => $this->getRequest()->getParam('id')]),
                'method' => 'post',
                'enctype' => 'multipart/form-data'
            ]
        ]);
        $form->setUseContainer(true);
        $this->setForm($form);
        $fieldset = $form->addFieldset('edit_form', array('legend' => 'Information'));

        //Add fields:
        $fieldset->addField('weight', 'text', array(
            'label' => 'Weight',
            'required' => true,
            'name' => 'weight',
            'style' => 'width:50px'
        ));
        $fieldset->addField('question', 'text', array(
            'label' => 'Question',
            'required' => true,
            'name' => 'question'
        ));
        //WYSIWYG Answer field
        $fieldset->addField('answer', 'editor', array(
            'name' => 'answer',
            'label' => __('Answer'),
            'title' => __('Answer'),
            'style' => 'height:15em',
            'config' => $this->cmsWysiwygConfig->getConfig(),
            'wysiwyg' => true,
            'required' => true
        ));
        $fieldset->addField('keywords', 'text', array(
            'label' => 'Keywords',
            'required' => false,
            'name' => 'keywords',
            'note' => 'separate keywords with a comma e.g keyword1, keyword2, keyword3'
        ));
        /*
         * Creates an array with all the available stores' id and frontname to populate the checkboxes field
         */

        $allStores = $this->_storeManager->getStores();
        foreach ($allStores as $stores) {
            $store = $this->_storeManager->getStore($stores);
            $values[] = array('value' => $store->getId(), 'label' => $store->getName());
        }

        $fieldset->addField('stores', 'checkboxes', array(
            'label' => 'Stores',
            'required' => true,
            'name' => 'stores[]',
            'values' => $values,
        ));

        $form->setValues($data);
        $this->setForm($form);

        //Dispatch an event 
        $this->eventManager->dispatch('adminhtml_faqs_edit_prepare_form', array('form' => $form));

        return parent::_prepareForm();
    }

}
