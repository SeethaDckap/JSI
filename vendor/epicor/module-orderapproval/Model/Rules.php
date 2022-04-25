<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Model;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime;
use Magento\Rule\Model\AbstractModel;
use Epicor\OrderApproval\Api\Data\GroupsInterface;

/**
 * Model Class for OrderApproval
 *
 * @category   Epicor
 * @package    Epicor_OrderApproval
 * @author     Epicor Websales Team
 *
 */
class Rules extends AbstractModel
{
    /**
     * @var \Epicor\OrderApproval\Model\Rules\Condition\CombineFactory
     */
    private $condCombineFactory;

    /**
     * @var \Magento\SalesRule\Model\Rule\Condition\Product\CombineFactory
     */
    private $condProdCombineF;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Epicor\OrderApproval\Model\Rules\Condition\CombineFactory $condCombineFactory,
        \Magento\SalesRule\Model\Rule\Condition\Product\CombineFactory $condProdCombineF
    ) {
        $this->condCombineFactory = $condCombineFactory;
        $this->condProdCombineF = $condProdCombineF;
        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $localeDate
        );
    }
    public function _construct()
    {
        $this->_init('Epicor\OrderApproval\Model\ResourceModel\Groups');
    }

    /**
     * Get rule condition combine model instance
     *
     * @return RoleModel\Erp\Condition\Combine
     */
    public function getConditionsInstance()
    {
        return $this->condCombineFactory->create();
    }

    /**
     * Get rule condition product combine model instance
     *
     * @return \Magento\SalesRule\Model\Rule\Condition\Product\Combine
     */
    public function getActionsInstance()
    {
        return $this->condProdCombineF->create();
    }

}
