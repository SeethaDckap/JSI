<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Quotes\Block\Adminhtml\Quotes\Edit;


class Form extends \Magento\Backend\Block\Widget\Form\Generic
{

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
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        $this->formFactory = $formFactory;
        $this->registry = $registry;
        parent::__construct($context,$registry, $formFactory,$data);
    }


    protected function _prepareForm()
    {
            $form = $this->_formFactory->create([
                'data' => [
                    'id' => 'edit_form',
                    'action' => $this->getUrl('*/*/save'),
                    'method' => 'post'
                ]
            ]);
        /*
        $form = $this->formFactory->create(array(
            'id' => 'edit_form',
            'action' => $this->getUrl('*\/\*\/save'),
            'method' => 'post',
            'enctype' => 'multipart/form-data'
        ));
        */
        $quote = $this->registry->registry('quote');
        $form->addField('entity_id', 'hidden', array(
            'name' => 'quote_id',
        ));
        if ($quote->getId()) {
            $form->setValues($quote->getData());
        }

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }

}
