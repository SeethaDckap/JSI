<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Model\Config\Budgets\Source;

use Epicor\OrderApproval\Model\Budgets\BudgetTypes as BudgetDurationTypes;
use Epicor\OrderApproval\Model\ResourceModel\ErpAccountBudget\CollectionFactory as ErpBudgetCollectionFactory;
use Epicor\OrderApproval\Model\ResourceModel\Groups\Budget\CollectionFactory as ShopperBudgetCollectionFactory;
use Epicor\OrderApproval\Model\ResourceModel\Groups\Budget\Collection as ShopperBudgetCollection;
use Epicor\OrderApproval\Model\ResourceModel\ErpAccountBudget\Collection as ErpBudgetCollection;

class BudgetTypes implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var ErpBudgetCollectionFactory
     */
    private $erpBudgetCollectionFactory;

    /**
     * @var array
     */
    private $budgetOptions = [];

    /**
     * @var ShopperBudgetCollectionFactory
     */
    private $shopperBudgetCollectionFactory;

    /**
     * BudgetTypes constructor.
     * @param ShopperBudgetCollectionFactory $shopperBudgetCollectionFactory
     * @param ErpBudgetCollectionFactory $erpBudgetCollectionFactory
     */
    public function __construct(
        ShopperBudgetCollectionFactory $shopperBudgetCollectionFactory,
        ErpBudgetCollectionFactory $erpBudgetCollectionFactory
    ) {
        $this->erpBudgetCollectionFactory = $erpBudgetCollectionFactory;
        $this->shopperBudgetCollectionFactory = $shopperBudgetCollectionFactory;
    }

    /**
     * Return array of options as value-label pairs, eg. value => label
     *
     * @return array
     */
    public function toOptionArray()
    {
        $budgetTypes = [];
        foreach ($this->budgetOptions as $type) {
            $budgetTypes[$type] = ucfirst($type);
        }
        return $budgetTypes;
    }

    /**
     * @param string $erpId
     * @return array
     */
    public function getRemainingErpOptions($erpId)
    {
        $currentTypes = $this->getCurrentErpTypes($erpId);
        $this->filterBudgetTypes($currentTypes);

        return $this->toOptionArray();
    }

    /**
     * @param string $erpId
     * @return array
     */
    public function getRemainingShopperOptions($groupId)
    {
        $currentTypes = $this->getCurrentShopperTypes($groupId);
        $this->filterBudgetTypes($currentTypes);

        return $this->toOptionArray();
    }

    /**
     * @param ErpBudgetCollection $currentTypes
     */
    private function filterBudgetTypes($currentTypes)
    {
        $savedTypes = [];
        foreach ($currentTypes as $budget) {
            $savedTypes[] = ucfirst($budget->getType());
        }
        $savedTypes = array_unique($savedTypes);

        $this->budgetOptions = array_diff(BudgetDurationTypes::getBudgetTypesList(), $savedTypes);
    }

    /**
     * @param string $erpId
     * @param string $currentSelected
     * @return array
     */
    public function getErpOptionValues($erpId, $currentSelected)
    {
        $options = $this->getRemainingErpOptions($erpId);

        return $this->renderOptionValues($options, $currentSelected);
    }

    /**
     * @param string $groupId
     * @param string $currentSelected
     * @return array
     */
    public function getShopperOptionValues($groupId, $currentSelected)
    {
        $options = $this->getRemainingShopperOptions($groupId);

        return $this->renderOptionValues($options, $currentSelected);
    }

    /**
     * @param array $options
     * @param string $currentSelected
     * @return array
     */
    private function renderOptionValues($options, $currentSelected)
    {
        $valueOptions = [];
        foreach ($options as $option) {
            $valueOptions[] = $this->setValueLabels($option);
        }
        if ($currentSelected) {
            $valueOptions[] = $this->setValueLabels($currentSelected);
        }

        return $valueOptions;
    }

    /**
     * @param string $option
     * @return array
     */
    private function setValueLabels($option)
    {
        return [
            'value' => strtolower($option),
            'label' => ucfirst($option)
        ];
    }

    /**
     * @param string $erpId
     * @return ErpBudgetCollection
     */
    private function getCurrentErpTypes($erpId)
    {
        /** @var  $budgetCollection ErpBudgetCollection*/
        $budgetCollection = $this->erpBudgetCollectionFactory->create();
        $budgetCollection
            ->addFieldToSelect('type')
            ->addFieldToFilter('erp_id', $erpId);

        return $budgetCollection;
    }

    /**
     * @param string $groupId
     * @return ErpBudgetCollection
     */
    private function getCurrentShopperTypes($groupId)
    {
        /** @var  $budgetCollection ErpBudgetCollection*/
        $budgetCollection = $this->shopperBudgetCollectionFactory->create();
        $budgetCollection
            ->addFieldToSelect('type')
            ->addFieldToFilter('group_id', $groupId);

        return $budgetCollection;
    }
}
