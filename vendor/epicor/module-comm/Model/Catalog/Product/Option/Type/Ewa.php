<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Catalog\Product\Option\Type;


class Ewa extends \Magento\Catalog\Model\Product\Option\Type\DefaultType
{

    /**
     * @var \Epicor\Comm\Block\Options\Type\Customview\EwaFactory
     */
    protected $commOptionsTypeCustomviewEwaFactory;

    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Epicor\Comm\Block\Options\Type\Customview\EwaFactory $commOptionsTypeCustomviewEwaFactory,
        array $data = []
    ) {
        $this->commOptionsTypeCustomviewEwaFactory = $commOptionsTypeCustomviewEwaFactory;
        parent::__construct(
            $checkoutSession,
            $scopeConfig,
            $data
        );
    }


    public function isCustomizedView()
    {
        return true;
    }

    public function getCustomizedView($optionInfo)
    {
        $customizeBlock = $this->commOptionsTypeCustomviewEwaFactory->create();
        $customizeBlock->setInfo($optionInfo);
        return $customizeBlock->toHtml();
    }

}
