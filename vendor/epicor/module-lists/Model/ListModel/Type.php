<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Model\ListModel;


/**
 * Model Class for List Types
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Type
{

    const LIST_TYPE_PRICE_LIST = 'Pr';
    const LIST_TYPE_CONTRACT = 'Co';
    const LIST_TYPE_FAVORITE = 'Fa';
    const LIST_TYPE_PRODUCT_GROUP = 'Pg';
    const LIST_TYPE_RESTRICTED_PURCHASE = 'Rp';
    const LIST_TYPE_RECENT_PURCHASE = 'Re';
    const LIST_TYPE_PREDEFINED_LIST = 'Pl';

    private $types = array(
        self::LIST_TYPE_CONTRACT => 'Contract',
        self::LIST_TYPE_FAVORITE => 'Favorites',
        self::LIST_TYPE_PREDEFINED_LIST => 'Predefined Lists',
        self::LIST_TYPE_PRICE_LIST => 'Price List',
        self::LIST_TYPE_PRODUCT_GROUP => 'Product Group',
        self::LIST_TYPE_RECENT_PURCHASE => 'Recent Purchases',
        self::LIST_TYPE_RESTRICTED_PURCHASE => 'Restricted Purchases'
    );
    private $instances = array(
        self::LIST_TYPE_PRICE_LIST => 'pricelist',
        self::LIST_TYPE_CONTRACT => 'contract',
        self::LIST_TYPE_FAVORITE => 'favorite',
        self::LIST_TYPE_PRODUCT_GROUP => 'productgroup',
        self::LIST_TYPE_RESTRICTED_PURCHASE => 'restrictedpurchase',
        self::LIST_TYPE_RECENT_PURCHASE => 'recentpurchase',
        self::LIST_TYPE_PREDEFINED_LIST => 'predefined'
    );
    private $newTypes = array(
        self::LIST_TYPE_PRICE_LIST,
        self::LIST_TYPE_FAVORITE,
        self::LIST_TYPE_PRODUCT_GROUP,
        self::LIST_TYPE_RESTRICTED_PURCHASE,
        self::LIST_TYPE_RECENT_PURCHASE,
        self::LIST_TYPE_PREDEFINED_LIST
    );
    private $qopTypes = array(
        self::LIST_TYPE_PRICE_LIST,
        self::LIST_TYPE_FAVORITE,
        self::LIST_TYPE_PRODUCT_GROUP,
        self::LIST_TYPE_PREDEFINED_LIST
    );
    private $listTypesCustomer = array(
        self::LIST_TYPE_FAVORITE => 'Favorites',
        self::LIST_TYPE_PREDEFINED_LIST => 'Predefined Lists',
        self::LIST_TYPE_PRODUCT_GROUP => 'Product Group'
    );

    /**
     * @var \Epicor\Lists\Model\ListModel\Type\Factory
     */
    protected $listsListModelTypeFactory;
    /**
     * @var \Epicor\Lists\Helper\Data
     */
    protected $listsHelper;

    public function __construct(
        \Epicor\Lists\Model\ListModel\TypeFactory $listsListModelTypeFactory,
        \Epicor\Lists\Helper\Admin $listsHelper
    ) {
        $this->listsListModelTypeFactory = $listsListModelTypeFactory;
        $this->listsHelper = $listsHelper;
    }
    /**
     * Returns array of types for use with select boxes
     * 
     * @return array
     */
    public function toOptionArray($new = false)
    {
        $types = array();
        foreach ($this->types as $value => $label) {
            if (!$new || in_array($value, $this->newTypes)) {
                $types[] = array('value' => $value, 'label' => $label . ' - ' . $value);
            }
        }

        return $types;
    }

    /**
     * Returns array of types for use with select boxes
     * 
     * @return array
     */
    public function toQopOptionArray()
    {
        $types = array();
        foreach ($this->types as $value => $label) {
            if (in_array($value, $this->qopTypes)) {
                $types[] = array('value' => $value, 'label' => $label . ' - ' . $value);
            }
        }

        return $types;
    }

    /**
     * Returns list label for given code
     * 
     * @return string
     */
    public function getListLabel($type)
    {
        foreach ($this->types as $value => $label) {
            if ($value == $type) {
                return $label;
            }
        }
    }

    /**
     * Returns array of types for use with grid filters
     *
     * @return array
     */
    public function toFilterArray()
    {
        return $this->types;
    }

    /**
     * Returns array of types for use with grid filters
     *
     * @return array
     */
    public function toQopFilterArray()
    {
        $types = array();
        foreach ($this->types as $value => $label) {
            if (in_array($value, $this->qopTypes)) {
                $types[$value] = $label;
            }
        }

        return $types;
    }

    /**
     * Returns array of types for use with grid frontend list filters
     *
     * @return array
     */
    public function toListFilterArray()
    {
        return $this->listTypesCustomer;
    }

    /**
     * Returns array of list types that are lists
     *
     * @return array
     */
    public function getListTypes()
    {
        $lists = array();
        foreach ($this->getTypeInstances() as $type => $instanceName) {
            //$instance = $this->listsListModelTypeFactory;
            $instance = $this->listsHelper->getListsTypeModelReader()->getModel($instanceName);
            /* @var $instance Epicor_Lists_Model_ListModel_Type_AbstractModel */
            if ($instance->isList()) {
                $lists[] = $type;
            }
        }

        return $lists;
    }

    /**
     * Returns array of list types that are contracts
     *
     * @return array
     */
    public function getContractTypes()
    {
        $contracts = array();
        foreach ($this->getTypeInstances() as $type => $instanceName) {     
            
            $instance = $this->listsHelper->getListsTypeModelReader()->getModel($instanceName);
            /* @var $instance Epicor_Lists_Model_ListModel_Type_AbstractModel */
            if ($instance->isContract()) {
                $contracts[] = $type;
            }
        }
        return $contracts;
    }

    /**
     * Returns array of instances
     *
     * @return array
     */
    public function getTypeInstances()
    {
        return $this->instances;
    }

    /**
     * Returns specific type instance string
     *
     * @return string
     */
    public function getTypeInstanceValue($type)
    {
        return isset($this->instances[$type]) ? $this->instances[$type] : false;
    }

}
