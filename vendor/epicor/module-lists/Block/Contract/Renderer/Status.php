<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Block\Contract\Renderer;


class Status extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\ConfigurableFactory
     */
    protected $configurableProductProductTypeConfigurableFactory;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\ConfigurableProduct\Model\Product\Type\ConfigurableFactory $configurableProductProductTypeConfigurableFactory,
        array $data = []
    ) {
        $this->configurableProductProductTypeConfigurableFactory = $configurableProductProductTypeConfigurableFactory;
        parent::__construct(
            $context,
            $data
        );
    }


    public function render(\Magento\Framework\DataObject $row)
    {
        $nowTime = time();
        $startDate = strtotime($row->getStartDate());
        $endDate = strtotime($row->getEndDate());
        $productStatusCode = $row->getStatus();

        $parentIds = $this->configurableProductProductTypeConfigurableFactory->create()->getParentIdsByChild($row->getEntityId());
        if ($parentIds || $row->getVisibility() == \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE) {
            return 'Not available on this Store';
        }


        if (in_array($row->getContractProductStatus(), array(0, NULL))) {
            return 'Inactive';
        }

        if (!$startDate && !$endDate) {
            return 'Active';
        }
        if ($startDate && $startDate > $nowTime) {
            return 'Pending';
        }
        if ($endDate && $endDate < $nowTime) {
            return 'Expired';
        }

        return 'Active';
    }

}
