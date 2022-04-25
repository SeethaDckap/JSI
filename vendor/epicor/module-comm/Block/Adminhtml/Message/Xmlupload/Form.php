<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Message\Xmlupload;


class Form extends \Magento\Backend\Block\Widget\Form\Generic
{

    const XML_FILE_UPLOAD = 1;
    const XML_TEXT_UPLOAD = 2;

    /**
     * @var \Epicor\Comm\Model\Config\Source\YesnoxmluploadFactory
     */
    protected $commConfigSourceYesnoxmluploadFactory;


    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Epicor\Comm\Model\Config\Source\YesnoxmluploadFactory $commConfigSourceYesnoxmluploadFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    )
    {
        $this->commConfigSourceYesnoxmluploadFactory = $commConfigSourceYesnoxmluploadFactory;
        $this->registry = $registry;
        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $data
        );
    }


    protected function _prepareForm()
    {
        $form = $this->_formFactory->create([
            'data' => [
                'id' => 'edit_form',
                'action' => $this->getUrl('*/*/upload'),
                'method' => 'post',
                'enctype' => 'multipart/form-data'
            ]
        ]);

        $form->setUseContainer(true);
        $this->setForm($form);

        $fieldset = $form->addFieldset(
            'layout_block_form', array(
                'legend' => __('XML Upload')
            )
        );

        $fieldset->addField(
            'input_type', 'select', array(
                'label' => __('Text or File?'),
                'name' => 'input_type',
                'required' => true,
                'values' => $this->commConfigSourceYesnoxmluploadFactory->create()->toOptionArray()
            )
        );

        $this
            ->setChild('form_after', $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Form\Element\Dependence')
                ->addFieldMap('input_type', 'input_type')
                ->addFieldMap('xml_file', 'xml_file')
                ->addFieldMap('xml_message', 'xml_message')
                ->addFieldDependence('xml_file', 'input_type', self::XML_FILE_UPLOAD)// 1 = 'Xml File'
                ->addFieldDependence('xml_message', 'input_type', self::XML_TEXT_UPLOAD) // 2 = 'Xml Text'
            );

        $fieldset->addField(
            'xml_file', 'file', array(
                'label' => __('XML Message File'),
                'required' => true,
                'name' => 'xml_file'
            )
        );

        $fieldset->addField(
            'xml_message', 'textarea', array(
                'label' => __('XML Message'),
                'required' => true,
                'name' => 'xml_message'
            )
        );

        $fieldset->addField(
            'post-xml', 'hidden', array(
                'label' => __('XML Message'),
                'required' => false,
                'name' => 'post-xml'
            )
        );

        //M1 > M2 Translation Begin (Rule 16)
        //$form->setValues($this->registry->registry('posted_xml_data'));
        //M1 > M2 Translation End
        return parent::_prepareForm();
    }

}
