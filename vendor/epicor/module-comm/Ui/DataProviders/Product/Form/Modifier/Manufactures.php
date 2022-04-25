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

class Manufactures extends AbstractModifier
{

    /**
     * @var ArrayManager
     */
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

        $fieldCode = 'ecc_manufacturers';
        $elementPath = $this->arrayManager->findPath($fieldCode, $meta, null, 'children');
        if (!$elementPath) {
            return $meta;
        }

        $manufacturesContainer['arguments']['data']['config'] = [
            'componentType' => Form\Fieldset::NAME,
            'label' => __('Manufactures'),
            'dataScope' => '',
            'breakLine' => false,
            'visible' => 1,
            'sortOrder' => 40,
        ];

        $manufacturesContainer = $this->arrayManager->set(
            'children',
            $manufacturesContainer,
            [
                'ecc_manufacturers' => $this->getDynamicRows(),
            ]
        );

        return $this->arrayManager->set($elementPath, $meta, $manufacturesContainer);
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

        return $this->arrayManager->set(
            'children',
            $record,
            [
                'container_name' => $this->getNameColumn(),
                'container_product_code' => $this->getProductCodeColumn(),
                'position' => $recordPosition,
                'action_delete' => $recordActionDelete,
            ]
        );
    }

    protected function getNameColumn()
    {
        $nameContainer['arguments']['data']['config'] = [
            'componentType' => Container::NAME,
            'formElement' => Container::NAME,
            'component' => 'Magento_Ui/js/form/components/group',
            'label' => __('Name'),
            'dataScope' => '',
        ];
        $nameField['arguments']['data']['config'] = [
            'formElement' => Form\Element\Input::NAME,
            'componentType' => Form\Field::NAME,
            'dataType' => Form\Element\DataType\Text::NAME,
            'dataScope' => 'name',
            'validation' => [
                'required-entry' => true,
            ],
        ];

        return $this->arrayManager->set('children/name', $nameContainer, $nameField);
    }


    protected function getProductCodeColumn()
    {
        $productCode['arguments']['data']['config'] = [
            'componentType' => Container::NAME,
            'formElement' => Container::NAME,
            'component' => 'Magento_Ui/js/form/components/group',
            'label' => __('Product Code'),
            'dataScope' => '',
        ];
        $productCodeField['arguments']['data']['config'] = [
            'formElement' => Form\Element\Input::NAME,
            'componentType' => Form\Field::NAME,
            'dataType' => Form\Element\DataType\Text::NAME,
            'dataScope' => 'product_code',
        ];

        return $this->arrayManager->set('children/product_code', $productCode, $productCodeField);
    }

}
//M1 > M2 Translation End