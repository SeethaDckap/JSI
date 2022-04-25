<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Helper\Frontend;

use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PublicCookieMetadata;

/**
 * Helper for List Products on the frontend
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Product extends \Epicor\Lists\Helper\Frontend
{

    protected $activeProductIds;
    protected $filterRequired;
    protected $_contracts;
    protected $groupConcatMaxLen;
    protected $listProducts;

    /**
     * @var \Epicor\Lists\Model\ListModel\Product\ConditionsFactory
     */
    protected $listsListModelProductConditionsFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $catalogResourceModelProductCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\ProductFactory
     */
    protected $catalogResourceModelProductFactory;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $_cookieManager;
    
    public function __construct(
        // FOR PARENT
        \Epicor\Lists\Helper\Context $context,
        \Epicor\Lists\Model\Contract\AddressFactory $listsContractAddressFactory,
        \Epicor\Lists\Model\ListFilterReader $filterReader,
        // FOR THIS CLASS
        \Epicor\Lists\Model\ListModel\Product\ConditionsFactory $listsListModelProductConditionsFactory,
        \Magento\Catalog\Model\ResourceModel\ProductFactory $catalogResourceModelProductFactory,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory
    ) {
        $this->listsListModelProductConditionsFactory = $listsListModelProductConditionsFactory;
        $this->catalogResourceModelProductCollectionFactory = $context->getCatalogResourceModelProductCollectionFactory();
        $this->catalogResourceModelProductFactory = $catalogResourceModelProductFactory;
        $listsSessionHelper = $context->getListsSessionHelper();        
        $customerAddressFactory = $context->getCustomerAddressFactory();
        $this->_cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        parent::__construct(
            $context, 
            $listsContractAddressFactory,
            $filterReader
        );
    }
    
    public function resetLists()
    {
        $this->filterRequired = null;
        $this->registry->unregister('epicor_lists_filter_products');

        $this->activeProductIds = null;
        $this->registry->unregister('epicor_lists_product_ids');

        $this->lists = null;
        $this->registry->unregister('epicor_lists_active_lists');

        $this->quickOrderPadLists = null;
        $this->registry->unregister('qop_lists');

        $this->contracts = null;
        $this->typeFilter = null;
        $this->typeReg = null;
    }

    /**
     * returns whether there are lists that will require products to be filtered out
     *
     * @return boolean
     */
    public function hasFilterableLists()
    {
        if (is_null($this->filterRequired)) {
            $this->filterRequired = false;
            $filterRequired = $this->registry->registry('epicor_lists_filter_products');
            if (is_null($filterRequired)) {
                $filterRequired = false;
                $lists = $this->getActiveLists();
                foreach ($lists as $list) {
                    /* @var $list Epicor_Lists_Model_ListModel */
                    if ($list->hasSetting('M') || $list->hasSetting('F')) {
                        $filterRequired = true;
                        break;
                    }
                }

                $this->registry->unregister('epicor_lists_filter_products');
                $this->registry->register('epicor_lists_filter_products', $filterRequired);
            }

            $this->filterRequired = $filterRequired;
        }

        return $this->filterRequired;
    }

    /**
     * Gets a comma delimited string of product ids
     *
     * @return string $errors
     */
    public function getActiveListsProductIds($asArray = false)
    {
        $contractHelper = $this->listsFrontendContractHelper;
        /* @var $contractHelper Epicor_Lists_Helper_Frontend_Contract */

        if (is_null($this->activeProductIds)) {
            $this->activeProductIds = '';
            $productIds = $this->registry->registry('epicor_lists_product_ids');
            if (is_null($productIds)) {
                $contracts = $contractHelper->getActiveContracts();
                if ($contractHelper->mustFilterByContract() && count($contracts) == 0) {
                    $productIds = '0';
                } else {
                    $productIds = $this->runProductsQuery();
                }
                $this->registry->unregister('epicor_lists_product_ids');
                $this->registry->register('epicor_lists_product_ids', $productIds);
            }

            $this->activeProductIds = $productIds;
        }

        return $asArray ? explode(',', $this->activeProductIds) : $this->activeProductIds;
    }

    /**
     * Gets a comma delimited string of product ids
     * 
     * @return string
     */
    protected function runProductsQuery()
    {
        $resource = $this->resourceConnection;
        /* @var $resource Mage_Core_Model_Resource */

        $table = $resource->getTableName('catalog_product_entity');

        $sqlBase = <<<SQL
            SELECT
                GROUP_CONCAT(DISTINCT entity_id SEPARATOR ',') as products
            FROM
                $table
            WHERE
                (%LISTSQL%);
SQL;

        $listSql = $this->getListSql();
        $productIds = '';

        if ($listSql) {
            $sql = str_replace('%LISTSQL%', $listSql, $sqlBase);
            $readConnection = $resource->getConnection('core_read');
            /* @var $readConnection Magento_Db_Adapter_Pdo_Mysql */

            $this->runGroupConcatMaxLenQuery();

            $query = $readConnection->query($sql);
            /* @var $query Zend_Db_Statement_Interface */
            $row = $query->fetch();
            $productIds = $row['products'];
        }

        return $productIds ?: 0;
    }

    /**
     * Runs a query to expand mysql session value grou_concat_max_len
     */
    protected function runGroupConcatMaxLenQuery()
    {
        $resource = $this->resourceConnection;
        /* @var $resource Mage_Core_Model_Resource */

        $readConnection = $resource->getConnection('core_read');
        /* @var $readConnection Magento_Db_Adapter_Pdo_Mysql */

        if (!$this->groupConcatMaxLen) {
            $table = $resource->getTableName('catalog_product_entity');

            $sql = <<<SQL
                SELECT 
                    COUNT(entity_id) as count_ids,
                    MAX(entity_id) as max_id
                FROM
                    $table;
SQL;
            $query = $readConnection->query($sql);
            /* @var $query Zend_Db_Statement_Interface */
            $row = $query->fetch();
            $countIds = $row['count_ids'];
            $maxId = $row['max_id'];
            $lenId = strlen($maxId);

            $this->groupConcatMaxLen = $countIds * ($lenId + 1);
        }

        $sql = 'SET SESSION group_concat_max_len = ' . $this->groupConcatMaxLen . ';';

        $readConnection->query($sql);
    }

    /**
     * Builds SQL for the lists to get product ids
     *
     * @return string
     */
    protected function getListSql()
    {
        $lists = $this->getActiveLists();

        $listQueries = array();

        foreach ($lists as $list) {
            /* @var $list Epicor_Lists_Model_ListModel */
            if (
                $list->hasSetting('M') == false &&
                $list->hasSetting('F') == false
            ) {
                continue;
            }

            if ($list->getType() == 'Co' || $list->getConditions())
                $listQueries[] = $this->getListProductQuery($list);
            else {
                $condition = $list->hasSetting('E') ? 'NOT IN' : 'IN';
                $listQueries['listId'][$list->getType()][$condition][] = $list->getId();
            }
        }

        if (!empty($listQueries['listId'])) {
            $resource2 = $this->resourceConnection;
            $listProductTable = $resource2->getTableName('ecc_list_product');
            $listSubQueries = array();
            foreach ($listQueries['listId'] as $listTypeValues) {
                foreach ($listTypeValues as $condition => $listValue) {
                    $listQueryBase = '( sku ' . $condition . ' (SELECT sku FROM ' . $listProductTable . ' WHERE list_id IN (%s)) )';
                    if ($condition == 'IN' && count($listValue) > 1) {
                        $listQueryBase = '( sku ' . $condition . ' (SELECT sku FROM ' . $listProductTable . ' WHERE list_id IN (%s) GROUP BY sku HAVING COUNT(*) = ' . count($listValue) . ') )';
                    }
                    $lists = implode(",", $listValue);
                    $listSubQueries[] = sprintf($listQueryBase, $lists);
                }
            }
            unset($listQueries['listId']);
            $listQueries = array_merge($listQueries, $listSubQueries);
        }

        $listQueries = $this->applyContractFilter($listQueries);
        return implode(' AND ', $listQueries);
    }

    /**
     * Generates an SQL snippet for the given list
     *
     * @param \Epicor\Lists\Model\ListModel $list
     * @return string
     */
    public function applyContractFilter($listQueries)
    {
        $helper = $this->listsFrontendContractHelper;
        /* @var $helper Epicor_Lists_Helper_Frontend_Contract */


        if ($helper->allowNonContractItems() == false) {

            $contracts = $helper->getActiveContracts();

            $selectedContract = $helper->getSelectedContract();

            if (isset($contracts[$selectedContract])) {
                $listQueries[] = $this->getListProductQuery($contracts[$selectedContract]);
            } else if ($contracts) {
                $contractQueries = array();
                foreach ($contracts as $contract) {
                    /* @var $contract Epicor_Lists_Model_ListModel */
                    $contractQueries[] = $this->getListProductQuery($contract);
                }
                $listQueries[] = '(' . implode(' OR ', $contractQueries) . ')';
            }
        }

        return $listQueries;
    }

    /**
     * Generates an SQL snippet for the given list
     * 
     * @param \Epicor\Lists\Model\ListModel $list
     * @return string
     */
    protected function getListProductQuery($list)
    {
        $resource = $this->resourceConnection;
        /* @var $resource Mage_Core_Model_Resource */
        $listProductTable = $resource->getTableName('ecc_list_product');

        if ($list->getType() == 'Co') {
            $contractProdTable = $resource->getTableName('ecc_contract_product');
            $productSql = 'SELECT sku FROM ' . $listProductTable . ' lp'
                . ', ' . $contractProdTable . ' cp'
                . ' WHERE lp.list_id = %s '
                . ' AND cp.list_product_id = lp.id '
                . ' AND cp.status = 1 '
                //M1 > M2 Translation Begin (Rule 25)
                //. ' AND (((`cp`.`start_date` <= \'' . now() . '\') '
                . ' AND (((`cp`.`start_date` <= \'' . date('Y-m-d H:i:s') . '\') '
                //M1 > M2 Translation End
                . 'OR (`cp`.`start_date` IS NULL)'
                . 'OR (`cp`.`start_date` = \'0000-00-00 00:00:00\')))'
                //M1 > M2 Translation Begin (Rule 25)
                //. 'AND (((`cp`.`end_date` >= \'' . now() . '\')'
                . 'AND (((`cp`.`end_date` >= \'' . date('Y-m-d H:i:s') . '\')'
                //M1 > M2 Translation End
                . 'OR (`cp`.`end_date` IS NULL)'
                . 'OR (`cp`.`end_date` = \'0000-00-00 00:00:00\')))';

            $listQueryBase = '( sku %s (' . $productSql . ') )';
        } else {
            $listQueryBase = '( sku %s (SELECT sku FROM ' . $listProductTable . ' WHERE list_id = %s) )';
        }

        $conditionQueryBase = '( sku %s (%s) )';

        $condition = $list->hasSetting('E') ? 'NOT IN' : 'IN';
        $listQuery = sprintf($listQueryBase, $condition, $list->getId());

        if ($list->getConditions()) {
            $conditionsModel = $this->listsListModelProductConditionsFactory->create();
            $conditionsModel->setListId($list->getId());
            $conditionsModel->setConditionsSerialized($list->getConditions());
            /* @var $conditionsModel Epicor_Lists_Model_ListModel_Product_Conditions */
             $conditionsSql = $conditionsModel->buildSql();
             $conditionsSql = str_replace('DISTINCT cpf.entity_id', 'DISTINCT cpf.sku', $conditionsSql);
             $conditionsSql = str_replace('SELECT `e`.*', 'SELECT DISTINCT cpf.sku', $conditionsSql);
             $conditionsSql = str_replace('`e`', '`cpf`', $conditionsSql);
             $conditionsSql = str_replace('`at_sku`.`value`', '`cpf`.`sku`', $conditionsSql);
            foreach ($this->_getChildCombineTablesToJoin($conditionsModel->getConditions()) as $alias=>$attribute) {
              $alias = explode('.',$alias);
              $conditionsSql = str_replace('`'.$alias[0].'`.`value`', '`cpf`.`'.$attribute.'`', $conditionsSql);        
            }
            
             
            if ($conditionsSql) {
                $join = $list->hasSetting('E') ? ' AND ' : ' OR ';
                $listQuery = '(' . $listQuery . $join . sprintf($conditionQueryBase, $condition, $conditionsSql) . ')';
            }
        }
        return $listQuery;
    }
    
     protected function _getChildCombineTablesToJoin($combine, $tables = [])
    {
        foreach ($combine->getConditions() as $condition) {
            
            if ($condition->getConditions()) {
                $tables = $this->_getChildCombineTablesToJoin($condition);
            } else {
                /** @var $condition AbstractCondition */
                $tables[$condition->getMappedSqlField()] = $condition->getAttributeObject()->getAttributeCode();
                foreach ($condition->getTablesToJoin() as $alias => $table) {
                    if (!isset($tables[$alias])) {
                        $tables[$alias] = $table;
                    }
                }
            }
        }
        return $tables;
    }

    /**
     * Gets the array of products for the given list 
     * 
     * @param \Epicor\Lists\Model\ListModel $list
     * @param booean $storeFilter
     * 
     * @return array
     */
    public function getProductIdsByList($list, $storeFilter = false)
    {
        $key = $list->getId() . '_' . intval($storeFilter);
        if (!isset($this->listProducts[$key])) {
            $resource = $this->resourceConnection;
            /* @var $resource Mage_Core_Model_Resource */

            $table = $resource->getTableName('catalog_product_entity');

            $sqlBase = <<<SQL
                SELECT
                    GROUP_CONCAT(DISTINCT entity_id SEPARATOR ',') as products
                FROM
                    $table
                WHERE                  
                    (%LISTSQL%)
SQL;

            $listSql = $this->getListProductQuery($list);
            $productIds = '';

            if ($storeFilter) {
                $sql = $this->getStoreProductFilterSql();
                $sqlBase .= ' AND entity_id IN(' . $sql . ')';
            }
            if ($listSql) {
                $sql = str_replace('%LISTSQL%', $listSql, $sqlBase);

                $readConnection = $resource->getConnection('core_read');
                /* @var $readConnection Magento_Db_Adapter_Pdo_Mysql */

                $this->runGroupConcatMaxLenQuery();

                $query = $readConnection->query($sql);
                /* @var $query Zend_Db_Statement_Interface */
                $row = $query->fetch();
                $productIds = $row['products'];
            }

            $this->listProducts[$key] = empty($productIds) ? array() : explode(',', $productIds);
        }

        return $this->listProducts[$key];
    }

    
    /**
     * Generates SQL to filter products for this store only
     * 
     * @return string
     */
    protected function getStoreProductFilterSql()
    {
        $sql = $this->registry->registry('list_product_store_sql');

        if (!$sql) {
            $collection = $this->catalogResourceModelProductCollectionFactory->create();
            /* @var $collection Mage_Catalog_Model_Resource_Product_Collection */

            $collection->setVisibility(array(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_CATALOG, \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH, \Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_SEARCH));
            $collection->addAttributeToFilter('status', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
            $collection->getSelect()->reset(\Zend_Db_Select::COLUMNS);
            $collection->getSelect()->columns('entity_id');

            $collection = $this->performLocationProductFiltering($collection);
            $collection = $this->performContractProductFiltering($collection);

            $sql = $collection->getSelectSql(true);
            $this->registry->register('list_product_store_sql', $sql);
        }
        return $sql;
    }
    
    /**
     * Gets Active Contracts for the current logged in Customer
     * For a given product ID
     *
     * @param \Epicor\Lists\Model\ListModel $list
     * @param string $file
     * @return array $errors
     */
    public function getContractsForProduct($productId)
    {
        if (isset($this->products[$productId]) == false) {
            $this->products[$productId] = array();
            $contractIds = $this->registry->registry('epicor_lists_product_contracts_' . $productId);
            if (is_null($contractIds)) {
                $this->getActiveLists();
                $contractIds = $this->getProductContractsBySql($productId);
                $this->registry->unregister('epicor_lists_product_contracts_' . $productId);
                $this->registry->register('epicor_lists_product_contracts_' . $productId, $contractIds);
            }

            $this->products[$productId] = array_flip($contractIds);
        }
        $lists = $this->getActiveLists();
        $contracts = array_intersect_key($lists, $this->products[$productId]);
        return $contracts;
    }

    /**
     * Gets a list of contract Id's applicable to a given product
     *
     * @param integer $productId
     *
     * @return array
     */
    public function getProductContractsBySql($productId)
    {
        $resource = $this->resourceConnection;
        /* @var $resource Mage_Core_Model_Resource */

        $listTable = $resource->getTableName('ecc_list');

        $sqlBase = <<<SQL
            SELECT
                GROUP_CONCAT(DISTINCT id SEPARATOR ',') as ids
            FROM
                $listTable
            WHERE
                (%LISTSQL%);
SQL;

        $listSql = $this->getListIdSql($productId);
        $contractIds = '';

        if ($listSql) {
            $sql = str_replace('%LISTSQL%', $listSql, $sqlBase);
            $readConnection = $resource->getConnection('core_read');
            /* @var $readConnection Magento_Db_Adapter_Pdo_Mysql */

            $this->runGroupConcatMaxLenQuery();

            $query = $readConnection->query($sql);
            /* @var $query Zend_Db_Statement_Interface */
            $row = $query->fetch();
            $contractIds = $row['ids'];
        }

        return explode(',', $contractIds) ?: array();
    }

    /**
     * Builds SQL for the lists to get lists for a product id
     *
     * @return string
     */
    protected function getListIdSql($productId)
    {
        $helper = $this->listsFrontendContractHelper;
        /* @var $helper Epicor_Lists_Helper_Frontend_Contract */

        $activeContracts = $helper->getActiveContracts();

        $sku = $this->catalogResourceModelProductFactory->create()->getAttributeRawValue($productId, 'sku', 0);
        $productSku = $sku['sku'];
        $resource = $this->resourceConnection;
        /* @var $resource Mage_Core_Model_Resource */
        $readConnection = $resource->getConnection('core_read');
        /* @var $readConnection Magento_Db_Adapter_Pdo_Mysql */
        $productSku = $readConnection->quote($productSku);
        $listProductTable = $resource->getTableName('ecc_list_product');
        $listQueryBase = '( id = %s AND (' . $productSku . ' %s (SELECT sku FROM ' . $listProductTable . ' WHERE list_id = %s) ))';
        $conditionQueryBase = '(id = %s AND (' . $productSku . ' %s (%s)) )';

        $listQueries = array();

        foreach ($activeContracts as $list) {
            /* @var $list Epicor_Lists_Model_ListModel */
            $condition = $list->hasSetting('E') ? 'NOT IN' : 'IN';
            $listQuery = sprintf($listQueryBase, $list->getId(), $condition, $list->getId());

            if ($list->getConditions()) {
                $conditionsModel = $this->listsListModelProductConditionsFactory->create();
                $conditionsModel->setListId($list->getId());
                $conditionsModel->setConditionsSerialized($list->getConditions());
                /* @var $conditionsModel Epicor_Lists_Model_ListModel_Product_Conditions */
                $conditionsSql = $conditionsModel->buildSql();
                $conditionsSql = str_replace('DISTINCT cpf.entity_id', 'DISTINCT cpf.sku', $conditionsSql);
                if ($conditionsSql) {
                    $listQuery = '(' . $listQuery . ' OR ' . sprintf($conditionQueryBase, $list->getId(), $condition, $conditionsSql) . ')';
                }
            }

            $listQueries[] = $listQuery;
        }

        return implode(' OR ', $listQueries);
    }

    public function activeContractsForProduct($productId)
    {
        if (!isset($this->_contracts[$productId])) {
            $contract = $this->listsFrontendContractHelper;
            $contracts = array();
            if ($this->scopeConfig->getValue('epicor_lists/global/enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
                $contracts = $this->getContractsForProduct($productId);
            }
            foreach ($contracts as $contract) {
                $this->_contracts[$productId][] = array('id' => $contract->getId(), 'title' => $contract->getTitle());
            }
        }
        if (isset($this->_contracts[$productId])) {
            return $this->_contracts[$productId];
        }
        return false;
    }

    /**
     * Looks for Contracts for specified product.
     * If it has one contract and usage is set to always returns contractId
     * If product can be added to cart without contract assigned returns true
     * If product cannot be added to cart without contract assigned returns false
     *
     * @param integer $productId
     * @param string $contractCode
     * 
     * @return bool|string
     */
    public function productIsValidForCart($productId, $contractCode = false)
    {
        $contractHelper = $this->listsFrontendContractHelper;
        /* @var $contractHelper Epicor_Lists_Helper_Frontend_Contract */

        if ($contractHelper->contractsDisabled()) {
            return true;
        }
        $isProductInContract = $this->isProductInContract($productId, $contractCode);
        if ($isProductInContract) {
            return true;
        }

        $valid = true;
        $contracts = $this->getContractsForProduct($productId);
        if ($contractHelper->mustFilterByContract()) {
            if ($contractCode !== false && !is_null($contractCode) && $isProductInContract === false) {
                $contracts = [];
            }
            if (empty($contracts)) {
                $valid = false;
            }
        } else if (empty($contracts) && $contractHelper->requiredContractType() == 'E') {
            $valid = false;
        }


        return $valid;
    }

    /**
     * Verifies if the Product is in an specific Contract by Erp Code
     *
     * @param integer $productId
     * @param string $contractCode
     * 
     * @return bool
     */
    public function isProductInContract($productId, $contractCode)
    {
        $contracts = $this->getContractsForProduct($productId);
        foreach ($contracts as $contract) {
            /* @var $contract Epicor_Lists_Model_ListModel */
            if ($contract->getErpCode() == $contractCode) {
                return true;
            }
        }

        return false;
    }
    
    /**
     * Returns cookieManager object to phtml.
     *
     * @return \Magento\Framework\Stdlib\CookieManagerInterface
     */
    public function getCookieManager(){
        return $this->_cookieManager;
    }

}
