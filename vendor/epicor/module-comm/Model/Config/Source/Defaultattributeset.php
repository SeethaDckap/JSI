<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Config\Source;


class Defaultattributeset
{

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory
     */
    protected $eavResourceModelEntityAttributeSetCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $catalogProductFactory;

    public function __construct(
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $eavResourceModelEntityAttributeSetCollectionFactory,
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory
    ) {
        $this->eavResourceModelEntityAttributeSetCollectionFactory = $eavResourceModelEntityAttributeSetCollectionFactory;
        $this->catalogProductFactory = $catalogProductFactory;
    }
    public function toOptionArray()
    {
//        $attributeSetCollection = Mage::getResourceModel('eav/entity_attribute_set_collection') ->load();
        $attributeSetCollection = $this->eavResourceModelEntityAttributeSetCollectionFactory->create()
            ->setEntityTypeFilter($this->catalogProductFactory->create()->getResource()->getTypeId());

        foreach ($attributeSetCollection as $value => $label) {
            $options[] = array('value' => $label->getAttributeSetName(), 'label' => $label->getAttributeSetName(), 'id' => $label->getAttributeSetId());
        }
        return $options;
    }

}
