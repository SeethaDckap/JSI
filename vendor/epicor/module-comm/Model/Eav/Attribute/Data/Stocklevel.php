<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Eav\Attribute\Data;


class Stocklevel extends \Magento\Eav\Model\Entity\Attribute\Source\Boolean
{

    /**
     * @var \Epicor\Comm\Model\Config\Source\StocklevelFactory
     */
    protected $commConfigSourceStocklevelFactory;

    public function __construct(
        \Magento\Eav\Model\ResourceModel\Entity\AttributeFactory $eavAttrEntity,
        \Epicor\Comm\Model\Config\Source\StocklevelFactory $commConfigSourceStocklevelFactory
    ) {
        $this->commConfigSourceStocklevelFactory = $commConfigSourceStocklevelFactory;
        parent::__construct(
            $eavAttrEntity
        );
    }


    /**
     * Retrieve all options array
     *
     * @return array
     */
    public function getAllOptions()
    {
        $this->_options = $this->commConfigSourceStocklevelFactory->create()->toOptionArray();
        return $this->_options;
    }

}
