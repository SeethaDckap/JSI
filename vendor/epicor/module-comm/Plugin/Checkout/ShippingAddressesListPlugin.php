<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Plugin\Checkout;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Model\AttributeMetadataDataProvider;

/**
 * Shipping address
 */
class ShippingAddressesListPlugin
{

    /**
     * @var AttributesFormCollection
     */
    private $attributesFormCollectionExist = null;
    /**
     * @var AddressMetadataInterface
     */
    private $addressMetadata;
    /**
     * @var AttributeMetadataDataProvider
     */
    private $attributeMetadataDataProvider;


    /**
     * @param AddressMetadataInterface $addressMetadata
     * @param AttributeMetadataDataProvider $attributeMetadataDataProvider
     */
    public function __construct(
        AddressMetadataInterface $addressMetadata,
        AttributeMetadataDataProvider $attributeMetadataDataProvider
    )
    {
        $this->addressMetadata               = $addressMetadata;
        $this->attributeMetadataDataProvider = $attributeMetadataDataProvider;
    }

    /**
     * Prepare Address.
     * @param \Magento\Customer\Model\Address\CustomerAddressDataFormatter $subject
     * @param array $result
     *
     * @return array
     */

    public function afterPrepareAddress(
        \Magento\Customer\Model\Address\CustomerAddressDataFormatter $subject,
        $result
    )
    {
        $notallowedattributes = $this->getNotallowedattributes();
        foreach ($notallowedattributes as $notallowedattribute) {
            unset($result['custom_attributes'][$notallowedattribute]);
        }
        return $result;
    }

    /**
     * Attribute Not to display in address.
     *
     * @return array
     */
    public function getNotallowedattributes()
    {
        if (!$this->attributesFormCollectionExist) {
            $allowed_attributes = [];
            $attributesFormCollection = $attributesMetadata = $this->addressMetadata->getAllAttributesMetadata();
            $attributesCollection = $this->attributeMetadataDataProvider->loadAttributesCollection(
                \Magento\Customer\Api\AddressMetadataInterface::ENTITY_TYPE_ADDRESS,
                'customer_address_edit'
            );
            foreach ($attributesCollection as $att) {
                $allowed_attributes[] = $att->getAttributecode();
            }
            foreach ($attributesFormCollection as $attributeMetadata) {
                $attributeCode = $attributeMetadata->getAttributeCode();
                if (!$attributeMetadata->isSystem() && !in_array($attributeCode, $allowed_attributes)
                    && !strpos($attributeCode, 'ecc_')) {
                    $this->attributesFormCollectionExist[] = $attributeCode;
                }
            }
        }
        return $this->attributesFormCollectionExist;

    }
}