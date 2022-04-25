<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
//M1 > M2 Translation Begin (Rule 18)
namespace Epicor\Comm\Ui\DataProviders\Product\Form\Modifier;


use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\DynamicRows;
use Magento\Ui\Component\Form;


class RelatedDocument extends AbstractModifier
{
    /**
     * @var ArrayManager
     */

   protected $_columns = array(
        'filename' => array(
            'type' => 'text',
            'formElement' => Form\Element\Input::NAME,
            'dataType' => Form\Element\DataType\Text::NAME,
            'label' => 'Filename'
        ),
        'description' => array(
            'type' => 'text',
            'formElement' => Form\Element\Input::NAME,
            'dataType' => Form\Element\DataType\Text::NAME,
            'label' => 'Description'
        ),
        'url' => array(
            'type' => 'text',
            'formElement' => Form\Element\Input::NAME,
            'dataType' => Form\Element\DataType\Text::NAME,
            'label' => 'Url'
        ),
        'attachment_number' => array(
            'type' => 'text',
            'formElement' => Form\Element\Input::NAME,
            'dataType' => Form\Element\DataType\Text::NAME,
            'label' => 'Attachment Number'
        ),
        'erp_file_id' => array(
            'type' => 'text',
            'formElement' => Form\Element\Input::NAME,
            'dataType' => Form\Element\DataType\Text::NAME,
            'label' => 'ERP File Id'
        ),
        'web_file_id' => array(
            'type' => 'text',
            'formElement' => Form\Element\Input::NAME,
            'dataType' => Form\Element\DataType\Text::NAME,
            'label' => 'Web File Id'
        ),
        'attachment_status' => array(
            'type' => 'text',
            'formElement' => Form\Element\Input::NAME,
            'dataType' => Form\Element\DataType\Text::NAME,
            'label' => 'Attachment Status'
        )
    );
    private $arrayManager;

    public function __construct(
        ArrayManager $arrayManager
    )
    {
        $this->arrayManager = $arrayManager;
    }


    public function modifyData(array $data)
    {
        return $data;
    }

    public function modifyMeta(array $meta)
    {

        $fieldCode = 'ecc_related_documents';
        $elementPath = $this->arrayManager->findPath($fieldCode, $meta, null, 'children');
        if (!$elementPath) {
            return $meta;
        }
        $relatedDocumentContainer['arguments']['data']['config'] = [
            'componentType' => Form\Fieldset::NAME,
            'label' => __('Related Documents'),
            'dataScope' => '',
            'breakLine' => false,
            'visible' => 1,
            'sortOrder' => 40,
        ];

        $relatedDocumentContainer= $this->arrayManager->set(
            'children',
        $relatedDocumentContainer,
            [
                'ecc_related_documents' => $this->getDynamicRows(),
            ]
        );
        
        return $this->arrayManager->set($elementPath, $meta,$relatedDocumentContainer);
    }

    protected function getDynamicRows()
    {
        $dynamicRows['arguments']['data']['config'] = [
            'addButtonLabel' => __('Add'),
            'componentType' => DynamicRows::NAME,
            'itemTemplate' => 'record',
            'renderDefaultRecord' => false,
            'columnsHeader' => true,
            'additionalClasses' => 'admin__field-wide',
            'dataScope' => '',
            'deleteProperty' => 'is_delete',
            'deleteValue' => '1',
            'template' => 'Epicor_Comm/relateddocuments/dynamic-rows/templates/default.html',
            'additionalButton' => true,
            'additionalButtonLabel' => 'Sync Documents',
            'additionalButtonOnClick' => 'msynchimage.syncFtpImages()'
        ];

        return $this->arrayManager->set('children/record', $dynamicRows, $this->getRecord());
    }

    protected function getRecord()
    {
        $record['arguments']['data']['config'] = [
            'componentType' => Container::NAME,
            'isTemplate' => true,
            'is_collection' => true,
            'component' => 'Magento_Ui/js/dynamic-rows/record',
            'dataScope' => '',
        ];
        $recordPosition['arguments']['data']['config'] = [
            'componentType' => Form\Field::NAME,
            'formElement' => Form\Element\Input::NAME,
            'dataType' => Form\Element\DataType\Number::NAME,
            'dataScope' => 'sort_order',
            'visible' => false,
        ];
        $recordActionDelete['arguments']['data']['config'] = [
            'label' => null,
            'componentType' => 'actionDelete',
            'fit' => true,
        ];

        $fileds = [];
        foreach($this->_columns as $_column=>$info){
            $fileds['container_'.$_column] = $this->getColumnsinfo($_column,$info);
        }
        $fileds['container_sync_required'] = $this->getSyncRequiredColumn();
        $fileds['container_is_erp_document'] = $this->getErpColumn();
        
        $fileds['position'] = $recordPosition;
        $fileds['action_delete'] = $recordActionDelete;
        return $this->arrayManager->set(
            'children',
            $record,
            $fileds
        );
    }

    public function getColumnsinfo($_column,$info)
    {
        $nameContainer['arguments']['data']['config'] = [
            'componentType' => Container::NAME,
            'formElement' => Container::NAME,
            'component' => 'Magento_Ui/js/form/components/group',
            'label' => __($info['label']),
            'dataScope' => '',
        ];
        $disabled=false;
        isset($info['disabled']) ? $disabled = $info['disabled']:$disabled=false;
        
        $nameField['arguments']['data']['config'] = [
            'formElement' => $info['formElement'],
            'componentType' => Form\Field::NAME,
            'dataType' => $info['dataType'],
            'dataScope' => $_column,
            'disabled' => $disabled
        ];

        return $this->arrayManager->set('children/filename', $nameContainer, $nameField);
    }

    public function getFileNameColumn()
    {
        $nameContainer['arguments']['data']['config'] = [
            'componentType' => Container::NAME,
            'formElement' => Container::NAME,
            'component' => 'Magento_Ui/js/form/components/group',
            'label' => __('FileName'),
            'dataScope' => '',
        ];
        $nameField['arguments']['data']['config'] = [
            'formElement' => Form\Element\Input::NAME,
            'componentType' => Form\Field::NAME,
            'dataType' => Form\Element\DataType\Text::NAME,
            'dataScope' => 'filename',
            'validation' => [
                'required-entry' => true,
            ],
        ];

        return $this->arrayManager->set('children/filename', $nameContainer, $nameField);
    }

    public function getDescriptionColumn()
    {
        $descriptionCode['arguments']['data']['config'] = [
            'componentType' => Container::NAME,
            'formElement' => Container::NAME,
            'component' => 'Magento_Ui/js/form/components/group',
            'label' => __('Description'),
            'dataScope' => '',
        ];
        $descriptionCodeField['arguments']['data']['config'] = [
            'formElement' => Form\Element\Input::NAME,
            'componentType' => Form\Field::NAME,
            'dataType' => Form\Element\DataType\Text::NAME,
            'dataScope' => 'description',
            'validation' => [
                'required-entry' => true,
            ],
        ];

        return $this->arrayManager->set('children/description', $descriptionCode, $descriptionCodeField);
    }


    public function getErpColumn()
    {
        $descriptionCode['arguments']['data']['config'] = [
            'componentType' => Container::NAME,
            'formElement' => Container::NAME,
            'component' => 'Magento_Ui/js/form/components/group',
            'label' => __('From ERP?'),
            'dataScope' => '',
        ];
        $descriptionCodeField['arguments']['data']['config'] = [
            'componentType' => \Magento\Ui\Component\Form\Field::NAME,
             'formElement' => \Magento\Ui\Component\Form\Element\Checkbox::NAME,
             'dataType' => \Magento\Ui\Component\Form\Element\DataType\Boolean::NAME,
            'dataScope' => 'is_erp_document',
            'disabled' => true,
            //'prefer' => 'toggle', //uncomment this line for On ANd OFF UI
            'valueMap' => [
                'true' => '1',
                'false' => '0'
            ],
        ];

        return $this->arrayManager->set('children/is_erp_document', $descriptionCode, $descriptionCodeField);
    }
    
    public function getSyncRequiredColumn()
    {
        $descriptionCode['arguments']['data']['config'] = [
            'componentType' => Container::NAME,
            'formElement' => Container::NAME,
            'component' => 'Magento_Ui/js/form/components/group',
            'label' => __('Sync Required'),
            'dataScope' => '',
        ];
        $descriptionCodeField['arguments']['data']['config'] = [
            'componentType' => \Magento\Ui\Component\Form\Field::NAME,
            'formElement' => \Magento\Ui\Component\Form\Element\Input::NAME,
            'dataType' => \Magento\Ui\Component\Form\Element\DataType\Text::NAME,
            'dataScope' => 'sync_required',
            'disabled' => true,
            'default' => "N"
        ];

        return $this->arrayManager->set('children/sync_required', $descriptionCode, $descriptionCodeField);
    }
}

//M1 > M2 Translation End