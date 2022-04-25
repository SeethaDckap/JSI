<?php
/**
 * Copyright © 2019-2020 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Import;

/**
 * Customer address import
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Address extends \Magento\CustomerImportExport\Model\Import\Address
{

    const COLUMN_CUSTOMER_ID = 'customer_id';
    const COLUMN_CUSTOMER_WEBSITE_ID = 'website_id';
    const COLUMN_CUSTOMER_ADDRESS_ID = 'entity_id';


    /**
     * Prepare data for add/update action
     *
     * @param array $rowData
     * @param string $action
     * @return array
     * @throws \Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _prepareAddressDataForUpdate(array $rowData, $action):array
    {
        // entity table data
        $entityRowNew = [];
        $entityRowUpdate = [];
        // attribute values
        $attributes = [];
        // customer default addresses
        $defaults = [];

        if (empty($rowData['erp_customer_group_code'])) {
            $multiSeparator = $this->getMultipleValueSeparator();
            $customerId = $rowData[self::COLUMN_CUSTOMER_ID];
            if ($action == 'insert' || !isset($rowData[self::COLUMN_CUSTOMER_ADDRESS_ID])) {

                $newAddress = true;
                $addressId = $this->_getNextEntityId();
            } else {
                $newAddress = false;
                $addressId = $rowData[self::COLUMN_CUSTOMER_ADDRESS_ID];
            }
            $entityRow = [
                'entity_id' => $addressId,
                'parent_id' => $customerId,
                'updated_at' => (new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT),
            ];

            foreach ($this->_attributes as $attributeAlias => $attributeParams) {
                if (array_key_exists($attributeAlias, $rowData)) {

                    if (!strlen($rowData[$attributeAlias])) {
                        if ($newAddress) {
                            $value = null;
                        } else {
                            continue;
                        }
                    } elseif ($newAddress && !strlen($rowData[$attributeAlias])) {
                    } elseif (in_array($attributeParams['type'], ['select', 'boolean'])) {
                        $value = $this->getSelectAttrIdByValue($attributeParams, mb_strtolower($rowData[$attributeAlias]));
                    } elseif ('datetime' == $attributeParams['type']) {
                        $value = (new \DateTime())->setTimestamp(strtotime($rowData[$attributeAlias]));
                        $value = $value->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
                    } elseif ('multiselect' == $attributeParams['type']) {
                        $ids = [];
                        foreach (explode($multiSeparator, mb_strtolower($rowData[$attributeAlias])) as $subValue) {
                            $ids[] = $this->getSelectAttrIdByValue($attributeParams, $subValue);
                        }
                        $value = implode(',', $ids);
                    } else {
                        $value = $rowData[$attributeAlias];
                    }
                    if ($attributeParams['is_static']) {
                        $entityRow[$attributeAlias] = $value;
                    } else {
                        $attributes[$attributeParams['table']][$addressId][$attributeParams['id']] = $value;
                    }
                }
            }
            foreach (self::getDefaultAddressAttributeMapping() as $columnName => $attributeCode) {
                if (!empty($rowData[$columnName]) || !empty($rowData['is_'.$attributeCode])) {
                    /** @var $attribute \Magento\Eav\Model\Entity\Attribute\AbstractAttribute */
                    $table = $this->_getCustomerEntity()->getResource()->getTable('customer_entity');
                    $defaults[$table][$customerId][$attributeCode] = $addressId;
                }
            }
            $entityRow['region_id'] = null;
            if (!empty($rowData[self::COLUMN_REGION])) {
                $countryNormalized = strtolower($rowData[self::COLUMN_COUNTRY_ID]);
                $regionNormalized = strtolower($rowData[self::COLUMN_REGION]);

                if (isset($this->_countryRegions[$countryNormalized][$regionNormalized])) {
                    $regionId = $this->_countryRegions[$countryNormalized][$regionNormalized];
                    $entityRow[self::COLUMN_REGION] = $this->_regions[$regionId];
                    $entityRow['region_id'] = $regionId;
                }
            }

            if ($newAddress) {
                $entityRowNew = $entityRow;
                $entityRowNew['created_at'] = (new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
            } else {
                $entityRowUpdate = $entityRow;
            }
        } else {
            $entityRowUpdate = $rowData;
        }
        return [
            'entity_row_new' => $entityRowNew,
            'entity_row_update' => $entityRowUpdate,
            'attributes' => $attributes,
            'defaults' => $defaults
        ];
    }

    /**
     * Import customer address data
     *
     * @abstract
     * @param $addressData
     * @param null $action
     * @return void
     * @throws \Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function importCustomerAddressData($addressData, $action = null)
    {
        if ($action == null) return;
        $newRows = [];
        $updateRows = [];
        $attributes = [];
        $defaults = [];

        if ($action != 'delete') {
            foreach ($addressData as $rowData) {
                $addUpdateResult = $this->_prepareAddressDataForUpdate($rowData, $action);
                if ($addUpdateResult['entity_row_new']) {
                    $newRows[] = $addUpdateResult['entity_row_new'];
                }
                if ($addUpdateResult['entity_row_update']) {
                    $updateRows[] = $addUpdateResult['entity_row_update'];
                }
                $attributes = $this->_mergeEntityAttributes($addUpdateResult['attributes'], $attributes);
                $defaults = $this->_mergeEntityAttributes($addUpdateResult['defaults'], $defaults);
            }
            if (!empty($rowData['erp_customer_group_code'])) {
                foreach ($updateRows as $row) {
                    $fields = array_diff(array_keys($row), ['entity_id', 'erp_code', 'created_at']);
                    $this->_connection->insertOnDuplicate('ecc_erp_account_address', $row, $fields);
                }
            } else {
                $this->_saveAddressEntities($newRows, $updateRows)
                    ->_saveAddressAttributes($attributes)
                    ->_saveCustomerDefaults($defaults);
            }
        } else {
            if (isset($addressData['erp_address'])
                && isset($addressData['cus_address'])
            ) {
                $this->_connection->delete('ecc_erp_account_address', ['entity_id IN (?)' => $addressData['erp_address']]);
                $addressData = $addressData['cus_address'];
            }
            if (!empty($addressData)) {
                $this->_deleteAddressEntities($addressData);
            }
        }
    }
}
