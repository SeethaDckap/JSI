<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Customer\Erpaccount\Create;


/**
 * Epicor_Comm_Block_Adminhtml_Customer_Erpaccount_Create_Form
 * 
 * Form for createing a new ERP Account
 * 
 * @author Gareth.James
 */
class Form extends \Magento\Backend\Block\Widget\Form
{

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $formFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        $this->formFactory = $formFactory;
        parent::__construct(
            $context,
            $data
        );
    }


    protected function _prepareForm()
    {
        $form = $this->formFactory->create(
            [
                'data' => array(
                    'id' => 'edit_form',
                    'action' => $this->getUrl('*/*/create'),
                    'method' => 'post'
                )
            ]
        );

        $form->setUseContainer(true);


        $this->setForm($form);
        return parent::_prepareForm();
    }

}
