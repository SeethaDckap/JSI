<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Block\Adminhtml\Listing\Csvupload;

use Epicor\Lists\Model\ListModel\TypeFactory;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\FormFactory;

/**
 * Class Form
 * @package Epicor\Lists\Block\Adminhtml\Listing\Csvupload
 */
class Form extends \Magento\Backend\Block\Widget\Form
{
    const XML_FILE_UPLOAD = 1;
    const XML_TEXT_UPLOAD = 2;

    /**
     * @var TypeFactory
     */
    protected $listsListModelTypeFactory;

    /**
     * @var FormFactory
     */
    protected $formFactory;

    /**
     * Form constructor.
     * @param Context $context
     * @param TypeFactory $listsListModelTypeFactory
     * @param FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        TypeFactory $listsListModelTypeFactory,
        FormFactory $formFactory,
        array $data = []
    ) {
        $this->formFactory = $formFactory;
        $this->listsListModelTypeFactory = $listsListModelTypeFactory;
        parent::__construct(
            $context,
            $data
        );
    }

    /**
     * @return Form
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        $listTypes = $this->listsListModelTypeFactory->create()->toOptionArray(true);
        $typeIndex = 'Type Index =';
        foreach ($listTypes as $type) {
            $typeIndex .= ' : ' . $type['label'] . ' ';
        }
        $erpLinkType = 'ERP Account Link Type Index = B2B - B : B2C - C : No Specific Link - N : Chosen ERP - E';

        $form = $this->formFactory->create(
            array('data' => array(
                    'id' => 'edit_form',
                    'action' => $this->getUrl('*/*/csvupload'),
                    'method' => 'post',
                    'enctype' => 'multipart/form-data',
                    'class'=> 'admin__scope-old'
                ))
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        $fieldset = $form->addFieldset(
            'layout_block_form',
            array(
            'legend' => __('CSV Upload')
            )
        );

        $fieldset->addField('productimportcsv', 'button', array(
            'value' => __('Download Example CSV File'),
            'onclick' => "return window.location = '" . $this->getUrl('epicor_lists/epicorlists_lists/createnewlistcsv') . "';",
            'name' => 'productimportcsv',
            'class' => 'form-button'
        ));

        $maxUpload = (int)(ini_get('max_file_uploads'));
        $fieldset->addField(
            'csv_file',
            'Epicor\Lists\Block\Adminhtml\Listing\Csvupload\Form\Field\File',
            array(
                'label' => __('CSV File'),
                'required' => true,
                'name' => 'csv_file[]',
                'accept' => '.csv',
                'note' => "A maximum of $maxUpload Csv files allowed to upload at a time."
            )
        );

        $fieldset->addField('typeindex', 'note', array(
            'text' => __('<br><b>' . $typeIndex . '</b><br><br><b>' . $erpLinkType . '</b>'),
        ));

        $fieldset->addFieldset(
            'base_fieldset',
            ['legend' => __('Mass Import Log')]
        );

        return parent::_prepareForm();
    }
}
