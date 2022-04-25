<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Form;


class AbstractBlock extends \Magento\Backend\Block\Widget\Form
{

    protected $idSearch = array(
        '][', ']', '['
    );
    protected $idReplace = array(
        '_', '_', '_'
    );

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        $this->layout = $context->getLayout();
        parent::__construct(
            $context,
            $data
        );
    }


    /**
     * Builds a form based on the config provided
     * 
     * @param array $config
     * 
     * @return void
     */
    protected function _buildForm($config)
    {

        $form = $this->getForm();

        foreach ($config as $fieldsetId => $fieldsetInfo) {

            $fieldset = $form->addFieldset($fieldsetId, array('legend' => $fieldsetInfo['label']));
            $fieldset->addType('heading', 'Epicor\Common\Lib\Varien\Data\Form\Element\Heading');

            foreach ($fieldsetInfo['fields'] as $fieldId => $field) {

                $field['required'] = isset($field['required']) ? $field['required'] : true;
                $field['name'] = isset($field['name']) ? $field['name'] : $fieldId;
                $field['type'] = isset($field['type']) ? $field['type'] : 'text';

                $fieldId = trim(str_replace($this->idSearch, $this->idReplace, $fieldId), '_');

                if (isset($field['renderer'])) {
                    $fieldset->addField($fieldId, $field['type'], $field)->setRenderer($this->layout->createBlock($field['renderer']));
                } else {
                    $fieldset->addField($fieldId, $field['type'], $field);
                }
            }
        }

        $this->setForm($form);
    }

}
