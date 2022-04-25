<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Punchout
 * @subpackage Ui
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Punchout\Ui\Component\Listing\Column\Attribute;

use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Options
 */
class Options implements OptionSourceInterface
{

    /**
     * Search criteria builder
     *
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * Attribute repository interface
     *
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;


    /**
     * Constructor
     *
     * @param SearchCriteriaBuilder        $searchCriteriaBuilder Search criteria builder.
     * @param AttributeRepositoryInterface $attributeRepository   Attribute repository interface.
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        AttributeRepositoryInterface $attributeRepository
    )
    {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->attributeRepository   = $attributeRepository;

    }//end __construct()


    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->getAllProductAttribute();

    }//end toOptionArray()


    /**
     * Get all product attribute array
     *
     * @return array
     */
    public function getAllProductAttribute()
    {
        $attribute      = [];
        $searchCriteria = $this->searchCriteriaBuilder->create();

        $attributeItems = $this->attributeRepository->getList(
            'catalog_product',
            $searchCriteria
        );

        foreach ($attributeItems->getItems() as $item) {
            $attribute[] = [
                'value' => $item->getAttributeCode(),
                'label' => $item->getAttributeCode(),
            ];
        }

        return $attribute;

    }//end getAllProductAttribute()


}//end class

