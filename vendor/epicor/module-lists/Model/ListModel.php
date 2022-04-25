<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Model;


use Magento\Bundle\Model\Product\Type;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * Model Class for List
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 *
 * @method string getType()
 * @method string getTitle()
 * @method string getLabel()
 * @method string getStartDate()
 * @method string getEndDate()
 * @method string getActive()
 * @method string getSource()
 * @method string getDefaultCurrency()
 * @method string getErpAccountLinkType()
 * @method string getErpAccountsExclusion()
 * @method string getIsDummy()
 * @method string getNotes()
 * @method string getPriority()
 * @method string getCreatedDate()
 * @method string getUpdatedDate()
 *
 * @method string setErpCode()
 * @method string setType()
 * @method string setTitle()
 * @method string setLabel()
 * @method string setStartDate()
 * @method string setEndDate()
 * @method string setActive()
 * @method string setSource()
 * @method string setDefaultCurrency()
 * @method string setErpAccountLinkType()
 * @method string setErpAccountsExclusion()
 * @method string setIsDummy()
 * @method string setNotes()
 * @method string setPriority()
 * @method string setCreatedDate()
 * @method string setUpdatedDate()
 */

class ListModel extends \Epicor\Database\Model\Lists  implements IdentityInterface
{

    const ACTION_ADD = 'add';
    const ACTION_REMOVE = 'remove';
    const ACTION_UPDATE = 'update';
    const KEY_ADDRESSES = 'addresses';
    const KEY_BRANDS = 'brands';
    const KEY_CONTRACT = 'contract';
    const KEY_CUSTOMERS = 'customer';
    const KEY_ERP_ACCOUNTS = 'erp_accounts';
    const KEY_PRICING = 'pricing';
    const KEY_PRODUCTS = 'products';
    const KEY_LABELS = 'labels';
    const KEY_STORE_GROUPS = 'store_groups';
    const KEY_WEBSITES = 'websites';
    const KEY_RESTRICTIONS = 'restrictions';
    const ERP_ACC_LINK_TYPE_B2B = 'B';
    const ERP_ACC_LINK_TYPE_B2C = 'C';
    const ERP_ACC_LINK_TYPE_CHOSEN = 'E';
    const ERP_ACC_LINK_TYPE_NONE = 'N';

    protected $_noCache = false;
    protected $_cache = array();
    protected $_changes = array();
    protected $_pricing = array();
    protected $_contract = null;
    protected $typeInstance;
    protected $sortedLabels;

    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;

    /**
     * @var \Epicor\Lists\Model\ListModel\LabelFactory
     */
    protected $listsListModelLabelFactory;

    /**
     * @var \Epicor\Lists\Model\ResourceModel\ListModel\Label\CollectionFactory
     */
    protected $listsResourceListModelLabelCollectionFactory;

    /**
     * @var \Epicor\Lists\Model\ContractFactory
     */
    protected $listsContractFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $catalogResourceModelProductCollectionFactory;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\CollectionFactory
     */
    protected $commResourceCustomerErpaccountCollectionFactory;

    /**
     * @var \Epicor\Lists\Model\ListModelFactory
     */
    protected $listsListModelFactory;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $customerResourceModelCustomerCollectionFactory;

    /**
     * @var \Epicor\Lists\Model\ResourceModel\ListModel\Address\CollectionFactory
     */
    protected $listsResourceListModelAddressCollectionFactory;

    /**
     * @var \Epicor\Lists\Model\ResourceModel\ListModel\Brand\CollectionFactory
     */
    protected $listsResourceListModelBrandCollectionFactory;

    /**
     * @var \Epicor\Lists\Model\ListModel\BrandFactory
     */
    protected $listsListModelBrandFactory;

    /**
     * @var \Epicor\Lists\Model\ListModel\AddressFactory
     */
    protected $listsListModelAddressFactory;

    /**
     * @var \Epicor\Lists\Helper\Data
     */
    protected $listsHelper;

    /**
     * @var \Epicor\Lists\Model\ListModel\ProductFactory
     */
    protected $listsListModelProductFactory;

    /**
     * @var \Epicor\Lists\Model\ResourceModel\ListModel\Product\CollectionFactory
     */
    protected $listsResourceListModelProductCollectionFactory;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var \Epicor\Lists\Model\ListModel\CustomerFactory
     */
    protected $listsListModelCustomerFactory;

    /**
     * @var \Epicor\Lists\Model\ResourceModel\ListModel\Customer\CollectionFactory
     */
    protected $listsResourceListModelCustomerCollectionFactory;

    /**
     * @var \Epicor\Lists\Model\ListModel\Erp\AccountFactory
     */
    protected $listsListModelErpAccountFactory;

    /**
     * @var \Epicor\Lists\Model\ResourceModel\ListModel\Erp\Account\CollectionFactory
     */
    protected $listsResourceListModelErpAccountCollectionFactory;

    /**
     * @var \Epicor\Lists\Model\ListModel\WebsiteFactory
     */
    protected $listsListModelWebsiteFactory;

    /**
     * @var \Epicor\Lists\Model\ResourceModel\ListModel\Website\CollectionFactory
     */
    protected $listsResourceListModelWebsiteCollectionFactory;

    /**
     * @var \Epicor\Lists\Model\ListModel\Store\GroupFactory
     */
    protected $listsListModelStoreGroupFactory;

    /**
     * @var \Epicor\Lists\Model\ResourceModel\ListModel\Store\Group\CollectionFactory
     */
    protected $listsResourceListModelStoreGroupCollectionFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTimeFactory
     */
    protected $dateTimeDateTimeFactory;

    /**
     * @var \Epicor\Lists\Model\ListModel\TypeFactory
     */
    protected $listsListModelTypeFactory;

    /**
     * @var \Epicor\Lists\Model\ListModel\Type\AbstractModelFactory
     */
    protected $listsListModelTypeAbstractFactory;

    /**
     * @var \Epicor\Lists\Model\ResourceModel\ListModel\CollectionFactory
     */
    protected $listsResourceListModelCollectionFactory;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Store\Model\ResourceModel\Website\CollectionFactory
     */
    protected $websiteCollectionFactory;


    protected $groupCollectionFactory;

     /**
     * @var \Epicor\Lists\Model\ListModel\Type\FavoriteFactory
     */
    protected $listsListModelTypeFavoriteFactory;

    /**
     * @var \Epicor\Lists\Model\ListModel\Type\ContractFactory
     */
    protected $listsListModelTypeContractFactory;

     /**
     * @var \Epicor\Lists\Model\ListModel\Type\PredefinedFactory
     */
    protected $listsListModelTypePredefinedFactory;

    /**
     * @var \Epicor\Lists\Model\ListModel\Type\PricelistFactory
     */
    protected $listsListModelTypePricelistFactory;

    /**
     * @var \Epicor\Lists\Model\ListModel\Type\ProductgroupFactory
     */
    protected $listsListModelTypeProductgroupFactory;

    /**
     * @var \Epicor\Lists\Model\ListModel\Type\RecentpurchaseFactory
     */
    protected $listsListModelTypeRecentpurchaseFactory;

    /**
     * @var \Epicor\Lists\Model\ListModel\Type\RestrictedpurchaseFactory
     */
    protected $listsListModelTypeRestrictedpurchaseFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    /**
     * @var ResourceModel\ListProductPosition
     */
    private $listProductPosition;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        \Epicor\Lists\Model\ListModel\LabelFactory $listsListModelLabelFactory,
        \Epicor\Lists\Model\ResourceModel\ListModel\Label\CollectionFactory $listsResourceListModelLabelCollectionFactory,
        \Epicor\Lists\Model\ContractFactory $listsContractFactory,
        \Epicor\Lists\Model\ResourceModel\Product\CollectionFactory $catalogResourceModelProductCollectionFactory,
        \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\CollectionFactory $commResourceCustomerErpaccountCollectionFactory,
        \Epicor\Lists\Model\ListModelFactory $listsListModelFactory,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerResourceModelCustomerCollectionFactory,
        \Epicor\Lists\Model\ResourceModel\ListModel\Address\CollectionFactory $listsResourceListModelAddressCollectionFactory,
        \Epicor\Lists\Model\ResourceModel\ListModel\Brand\CollectionFactory $listsResourceListModelBrandCollectionFactory,
        \Epicor\Lists\Model\ListModel\BrandFactory $listsListModelBrandFactory,
        \Epicor\Lists\Model\ListModel\AddressFactory $listsListModelAddressFactory,
        \Epicor\Lists\Helper\Data $listsHelper,
        \Epicor\Lists\Model\ListModel\ProductFactory $listsListModelProductFactory,
        \Epicor\Lists\Model\ResourceModel\ListModel\Product\CollectionFactory $listsResourceListModelProductCollectionFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Epicor\Lists\Model\ListModel\CustomerFactory $listsListModelCustomerFactory,
        \Epicor\Lists\Model\ResourceModel\ListModel\Customer\CollectionFactory $listsResourceListModelCustomerCollectionFactory,
        \Epicor\Lists\Model\ListModel\Erp\AccountFactory $listsListModelErpAccountFactory,
        \Epicor\Lists\Model\ResourceModel\ListModel\Erp\Account\CollectionFactory $listsResourceListModelErpAccountCollectionFactory,
        \Epicor\Lists\Model\ListModel\WebsiteFactory $listsListModelWebsiteFactory,
        \Epicor\Lists\Model\ResourceModel\ListModel\Website\CollectionFactory $listsResourceListModelWebsiteCollectionFactory,
        \Epicor\Lists\Model\ListModel\Store\GroupFactory $listsListModelStoreGroupFactory,
        \Epicor\Lists\Model\ResourceModel\ListModel\Store\Group\CollectionFactory $listsResourceListModelStoreGroupCollectionFactory,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateTimeDateTimeFactory,
        \Epicor\Lists\Model\ListModel\TypeFactory $listsListModelTypeFactory,
        \Epicor\Lists\Model\ListModel\Type\AbstractModelFactory $listsListModelTypeAbstractFactory,
        \Epicor\Lists\Model\ResourceModel\ListModel\CollectionFactory $listsResourceListModelCollectionFactory,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Store\Model\ResourceModel\Website\CollectionFactory $websiteCollectionFactory,
        \Magento\Store\Model\ResourceModel\Group\CollectionFactory $groupCollectionFactory,
        \Epicor\Lists\Model\ListModel\Type\ContractFactory $listsListModelTypeContractFactory,
        \Epicor\Lists\Model\ListModel\Type\FavoriteFactory $listsListModelTypeFavoriteFactory,
        \Epicor\Lists\Model\ListModel\Type\PredefinedFactory $listsListModelTypePredefinedFactory,
        \Epicor\Lists\Model\ListModel\Type\PricelistFactory $listsListModelTypePricelistFactory,
        \Epicor\Lists\Model\ListModel\Type\ProductgroupFactory $listsListModelTypeProductgroupFactory,
        \Epicor\Lists\Model\ListModel\Type\RecentpurchaseFactory $listsListModelTypeRecentpurchaseFactory,
        \Epicor\Lists\Model\ListModel\Type\RestrictedpurchaseFactory $listsListModelTypeRestrictedpurchaseFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        \Epicor\Lists\Model\ResourceModel\ListProductPosition $listProductPosition = null
    ) {
        $this->groupCollectionFactory = $groupCollectionFactory;
        $this->websiteCollectionFactory = $websiteCollectionFactory;
        $this->commMessagingHelper = $commMessagingHelper;
        $this->listsListModelLabelFactory = $listsListModelLabelFactory;
        $this->listsResourceListModelLabelCollectionFactory = $listsResourceListModelLabelCollectionFactory;
        $this->listsContractFactory = $listsContractFactory;
        $this->catalogResourceModelProductCollectionFactory = $catalogResourceModelProductCollectionFactory;
        $this->commResourceCustomerErpaccountCollectionFactory = $commResourceCustomerErpaccountCollectionFactory;
        $this->listsListModelFactory = $listsListModelFactory;
        $this->customerResourceModelCustomerCollectionFactory = $customerResourceModelCustomerCollectionFactory;
        $this->listsResourceListModelAddressCollectionFactory = $listsResourceListModelAddressCollectionFactory;
        $this->listsResourceListModelBrandCollectionFactory = $listsResourceListModelBrandCollectionFactory;
        $this->listsListModelBrandFactory = $listsListModelBrandFactory;
        $this->listsListModelAddressFactory = $listsListModelAddressFactory;
        $this->listsHelper = $listsHelper;
        $this->listsListModelProductFactory = $listsListModelProductFactory;
        $this->listsResourceListModelProductCollectionFactory = $listsResourceListModelProductCollectionFactory;
        $this->resourceConnection = $resourceConnection;
        $this->listsListModelCustomerFactory = $listsListModelCustomerFactory;
        $this->listsResourceListModelCustomerCollectionFactory = $listsResourceListModelCustomerCollectionFactory;
        $this->listsListModelErpAccountFactory = $listsListModelErpAccountFactory;
        $this->listsResourceListModelErpAccountCollectionFactory = $listsResourceListModelErpAccountCollectionFactory;
        $this->listsListModelWebsiteFactory = $listsListModelWebsiteFactory;
        $this->listsResourceListModelWebsiteCollectionFactory = $listsResourceListModelWebsiteCollectionFactory;
        $this->listsListModelStoreGroupFactory = $listsListModelStoreGroupFactory;
        $this->listsResourceListModelStoreGroupCollectionFactory = $listsResourceListModelStoreGroupCollectionFactory;
        $this->dateTimeDateTimeFactory = $dateTimeDateTimeFactory;
        $this->listsListModelTypeFactory = $listsListModelTypeFactory;
        $this->listsListModelTypeAbstractFactory = $listsListModelTypeAbstractFactory;
        $this->listsResourceListModelCollectionFactory = $listsResourceListModelCollectionFactory;
        $this->request = $request;
        $this->listsListModelTypeContractFactory = $listsListModelTypeContractFactory;
        $this->listsListModelTypeFavoriteFactory = $listsListModelTypeFavoriteFactory;
        $this->listsListModelTypePredefinedFactory = $listsListModelTypePredefinedFactory;
        $this->listsListModelTypePricelistFactory = $listsListModelTypePricelistFactory;
        $this->listsListModelTypeProductgroupFactory = $listsListModelTypeProductgroupFactory;
        $this->listsListModelTypeRecentpurchaseFactory = $listsListModelTypeRecentpurchaseFactory;
        $this->listsListModelTypeRestrictedpurchaseFactory = $listsListModelTypeRestrictedpurchaseFactory;
        $this->registry = $registry;

        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
        $this->listProductPosition = $listProductPosition;
    }


    public function _construct()
    {
        $this->_init('Epicor\Lists\Model\ResourceModel\ListModel');
    }

    /**
     * Gets the ERP code
     *
     * For contracts, behind the scenes the ERP code is a combo
     * of erp account & contract code
     *
     * @return string
     */
    public function getErpCode()
    {
        if ($this->getType() == 'Co') {
            $helper = $this->commMessagingHelper;
            /* @var $helper Epicor_Comm_Helper_Messaging */
            return $helper->getUom($this->getData('erp_code'));
        } else {
            return $this->getData('erp_code');
        }
    }

    public function afterSave()
    {
        $this->_saveBrands();
        $this->_saveAddresses();
        $this->_saveProducts();
        $this->_saveCustomers();
        $this->_saveErpAccounts();
        $this->_saveWebsites();
        $this->_saveLabels();
        $this->_saveStoreGroups();
        $this->_saveContract();
        //$this->_saveRestrictions();
        $this->clearCache();


        parent::afterSave();
    }

    /**
     * Gets Settings for the list
     *
     * @return array
     */
    public function getSettings()
    {
        $settings = $this->getData('settings');
        return empty($settings) ? array() : str_split($settings);
    }

    /**
     * Sets Settings for the list
     *
     * @return \Epicor\Lists\Model\ListModel
     */
    public function setSettings($settings)
    {
        $settings = is_array($settings) ? implode('', $settings) : $settings;
        $this->setData('settings', $settings);
        return $this;
    }

    /**
     * Returns true if the Setting Flag is active
     *
     * @return bool
     */
    public function hasSetting($setting)
    {
        return in_array(strtoupper($setting), $this->getSettings());
    }

    /**
     * Gets ERP Override for the list
     *
     * @return array
     */
    public function getErpOverride()
    {
        $override = $this->getData('erp_override');
        return empty($override) ? array() : unserialize($override);
    }

    /**
     * Sets ERP Override for the list
     *
     * @return \Epicor\Lists\Model\ListModel
     */
    public function setErpOverride($override)
    {
        $this->setData('erp_override', serialize($override));
        return $this;
    }

    /**
     * Adds a Website-Level Label
     *
     * @param integer $websiteId
     * @param string $label
     *
     * @return \Epicor_Lists_Model_ListModel
     */
    public function addWebsiteLabel($websiteId, $label)
    {
        return $this->addLabel('website', $websiteId, $label);
    }

    /**
     * Adds a Store Group-Level Label
     *
     * @param integer $groupId
     * @param string $label
     *
     * @return \Epicor_Lists_Model_ListModel
     */
    public function addStoreGroupLabel($groupId, $label)
    {
        return $this->addLabel('store_group', $groupId, $label);
    }

    /**
     * Adds a Store-Level Label
     *
     * @param integer $storeId
     * @param string $label
     *
     * @return \Epicor_Lists_Model_ListModel
     */
    public function addStoreLabel($storeId, $label)
    {
        return $this->addLabel('store', $storeId, $label);
    }

    /**
     * Adds a Type Specific label
     *
     * @param string $type
     * @param integer $typeId
     * @param string $label
     *
     * @return \Epicor_Lists_Model_ListModel
     */
    protected function addLabel($type, $typeId, $label)
    {
        $sortedLabels = $this->getSortedLabels();
        $labels = $this->getLabels();
        $typeKey = $type . 's';
        if (isset($sortedLabels[$typeKey][$typeId])) {
            $labelModel = $labels[$sortedLabels[$typeKey][$typeId]];
            if ($label != $labelModel->getLabel()) {
                $labelModel->setLabel($label);
                $this->_labelChanges($labelModel, self::KEY_LABELS, self::ACTION_UPDATE);
            }
        } else {
            $labelModel = $this->listsListModelLabelFactory->create();
            /* @var $labelModel Epicor_Lists_Model_ListModel_Label */
            $labelModel->setData($type . '_id', $typeId);
            $labelModel->setListId($this->getId());
            $labelModel->setLabel($label);
            $this->_labelChanges($labelModel, self::KEY_LABELS, self::ACTION_ADD);
        }
        return $this;
    }

    /**
     * Removes a Website Specific label
     *
     * @param integer $websiteId
     *
     * @return \Epicor_Lists_Model_ListModel
     */
    public function removeWebsiteLabel($websiteId)
    {
        return $this->removeLabel('website', $websiteId);
    }

    /**
     * Removes a Store Group Specific label
     *
     * @param integer $groupId
     *
     * @return \Epicor_Lists_Model_ListModel
     */
    public function removeStoreGroupLabel($groupId)
    {
        return $this->removeLabel('store_group', $groupId);
    }

    /**
     * Removes a Store Specific label
     *
     * @param integer $storeId
     *
     * @return \Epicor_Lists_Model_ListModel
     */
    public function removeStoreLabel($storeId)
    {
        return $this->removeLabel('store', $storeId);
    }

    /**
     * Removes a Type Specific label
     *
     * @param string $type
     * @param integer $typeId
     *
     * @return \Epicor_Lists_Model_ListModel
     */
    public function removeLabel($type, $typeId)
    {
        $sortedLabels = $this->getSortedLabels();
        $typeKey = $type . 's';
        if (isset($sortedLabels[$typeKey][$typeId])) {
            $this->_labelChanges($sortedLabels[$typeKey][$typeId], self::KEY_LABELS, self::ACTION_REMOVE);
        }

        return $this;
    }

    /**
     * Retrives labels for the list, organised by level (website/group/store)
     *
     * @return array $items
     */
    public function getSortedLabels()
    {
        if (is_null($this->sortedLabels)) {
            $labels = array(
                'websites' => array(),
                'store_groups' => array(),
                'stores' => array(),
            );

            foreach ($this->getLabels() as $item) {
                /* @var $item Epicor_Lists_Model_ListModel_Label */
                if ($item->getWebsiteId()) {
                    $labels['websites'][$item->getWebsiteId()] = $item->getId();
                } else if ($item->getStoreGroupId()) {
                    $labels['store_groups'][$item->getStoreGroupId()] = $item->getId();
                } else if ($item->getStoreId()) {
                    $labels['stores'][$item->getStoreId()] = $item->getId();
                }
            }

            $this->sortedLabels = $labels;
        }

        return $this->sortedLabels;
    }

    /**
     * Retrives the label for a store for the list
     *
     * @return string $label
     */
    public function getStoreLabel($store)
    {
        $sortedLabels = $this->getSortedLabels();
        $labels = $this->getLabels();

        $storeId = $store->getId();
        $storeGroupId = $store->getGroupId();
        $websiteId = $store->getWebsiteId();

        $labelId = false;
        if (isset($sortedLabels['store'][$storeId])) {
            $labelId = $sortedLabels['store'][$storeId];
        } elseif (isset($sortedLabels['store_groups'][$storeGroupId])) {
            $labelId = $sortedLabels['store_groups'][$storeGroupId];
        } elseif (isset($sortedLabels['websites'][$websiteId])) {
            $labelId = $sortedLabels['websites'][$websiteId];
        }

        if ($labelId && isset($labels[$labelId])) {
            $label = $labels[$labelId]->getLabel();
        } else {
            $label = $this->getLabel();
        }

        return $label;
    }

    /**
     * Retrives labels for the list
     *
     * @return array $items
     */
    public function getLabels()
    {
        $cacheKey = self::KEY_LABELS;
        if ($cache = $this->_getCachedData($cacheKey)) {
            return $cache;
        }

        $collection = $this->listsResourceListModelLabelCollectionFactory->create();
        /* @var $collection Epicor_Lists_Model_Resource_ListModel_Label_Collection */
        $collection->addFieldtoFilter('list_id', $this->getId());

        $items = array();
        foreach ($collection->getItems() as $item) {
            /* @var $item Epicor_Lists_Model_ListModel_Label */
            $items[$item->getId()] = $item;
        }

        $this->_cacheData($cacheKey, $items);

        return $items;
    }

    /**
     * Retrives list's contract
     *
     * @return \Epicor\Lists\Model\Contract $contract
     */
    public function getContract()
    {
        $cacheKey = self::KEY_CONTRACT;
        if ($cache = $this->_getCachedData($cacheKey)) {
            return $cache;
        }

        $contract = $this->listsContractFactory->create()->load($this->getId(), 'list_id');
        /* @var $contract Epicor_Lists_Model_Contract */

        if ($contract->isObjectNew()) {
            $contract->setListId($this->getId());
        }

        $this->_cacheData($cacheKey, $contract);

        $this->_contract = $contract;

        return $contract;
    }

    /**
     * Retrives products from the list
     *
     * @return array $items
     */
    public function getProducts($id = false, $cacheSet = true)
    {
        if ($id) {
            $this->setId($id);
        }

        $cacheKey = self::KEY_PRODUCTS;
        if (($cache = $this->_getCachedData($cacheKey)) && ($cacheSet)) {
            return $cache;
        }

        $collection = $this->catalogResourceModelProductCollectionFactory->create();
        /* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
        $collection->addAttributeToSelect('sku');
        //allow duplication of product entity ids
        $collection->setFlag('allow_duplicate', 1);
        $collection->getSelect()->join(
            array('list' => $collection->getTable('ecc_list_product')), 'e.sku = list.sku AND list.list_id = "' . $this->getId() . '"', array()
        );
        $collection->setFlag('no_product_filtering', true);
        $items = array();
        foreach ($collection->getItems() as $product) {
            $items[$product->getSku()] = $product;
        }
        $this->_cacheData($cacheKey, $items);

        return $items;
    }

    /**
     * Retrives Erp Accounts from the list
     *
     * @return array $items
     */
    public function getErpAccounts($id = false)
    {
        $cacheKey = false;
        if ($id) {
            $this->setId($id);
        } else {
            $cacheKey = self::KEY_ERP_ACCOUNTS;
            if ($cache = $this->_getCachedData($cacheKey)) {
                return $cache;
            }
        }

        $collection = $this->commResourceCustomerErpaccountCollectionFactory->create();
        /* @var $collection Epicor_Comm_Model_ResourceCustomer_Erpaccount */
        $collection->getSelect()->join(
            array('list' => $collection->getTable('ecc_list_erp_account')),
            'main_table.entity_id = list.erp_account_id AND list.list_id = "' . $this->getId() . '"',
            array()
        );

        $items = array();
        foreach ($collection->getItems() as $item) {
            $items[$item->getId()] = $item;
        }
        if ($cacheKey) {
            $this->_cacheData($cacheKey, $items);
        }


        return $items;
    }

    /**
     * Gets the list of ERP Accounts, updated with changes
     *
     * @return type
     */
    public function getErpAccountsWithChanges()
    {
        $cacheKey = self::KEY_ERP_ACCOUNTS . '_UPDATED';
        if ($cache = $this->_getCachedData($cacheKey)) {
            return $cache;
        }

        $erpAccounts = $this->getErpAccounts();
        if (isset($this->_changes[self::KEY_ERP_ACCOUNTS])) {
            foreach ($this->_changes[self::KEY_ERP_ACCOUNTS] as $type => $items) {
                if ($type == self::ACTION_ADD) {
                    $collection = $this->commResourceCustomerErpaccountCollectionFactory->create();
                    /* @var $collection Epicor_Comm_Model_Resource_Customer_Erpaccount */
                    $collection->addFieldToFilter('entity_id', array_keys($items));
                    $erpAccounts = $erpAccounts + $collection->getItems();
                } else if ($type == self::ACTION_REMOVE) {
                    foreach ($items as $key => $item) {
                        if (isset($erpAccounts[$key])) {
                            unset($erpAccounts[$key]);
                        }
                    }
                }
            }
        }

        $this->_cacheData($cacheKey, $erpAccounts);
        return $erpAccounts;
    }

    /**
     * Validates if the erp account exists in the list
     *
     * @param \Epicor\Comm\Model\Customer\Erpaccount $erpAccount
     * @return bool
     */
    public function isValidForErpAccount($erpAccount)
    {
        $valid = true;

        $erpAccounts = $this->getErpAccounts();
        if (count($erpAccounts) > 0) {
            return isset($erpAccounts[$erpAccount->getId()]);
        }

        switch ($this->getErpAccountLinkType()) {
            case 'B':
                $valid = $erpAccount->getAccountType() == 'B2B';
                break;
            case 'C':
                $valid = $erpAccount->getAccountType() == 'B2C';
                break;
            case 'E':
                $valid = false;
                break;
        }

        return $valid;
    }

    /**
     * Validates  whether the user edit the list or not
     *
     * @param \Epicor\Comm\Model\Customer\Erpaccount $erpAccount
     * @return bool
     */
    public function isValidEditForErpAccount($customer, $id = false)
    {
        $isMasterShoper = $customer->getData('ecc_master_shopper');
        if ($isMasterShoper) {
            $erpAccounts = array_keys($this->getErpAccounts($id));
            if (count($erpAccounts) > 1) {
                $valid = false;
            } else {
                $valid = true;
            }
        } else {
            $valid = true;
        }
        return $valid;
    }

    /**
     * Validates  whether the user edit the customers or not (assigning customers)
     *
     * @param \Epicor\Comm\Model\Customer\Erpaccount $erpAccount
     * @return bool
     */
    public function isValidEditForCustomers($customer, $id = null, $checkOwnerId = null)
    {
        $isMasterShoper = $customer->getData('ecc_master_shopper');
        if (!$isMasterShoper) {
            $customers = $this->getCustomers($id);
            $customerId = $customer->getId();
            $assignedCustomers = isset($customers[$customer->getId()]) ? $customers[$customer->getId()] : null;
//            if ((count($customers) > 1) || ($customerId != $checkOwnerId) || (empty($assignedCustomers))) {
            if ((count($customers) > 1) || ($customerId != $checkOwnerId)) {
                $valid = false;
            } else {
                $valid = true;
            }
        } else {
            $valid = true;
        }
        return $valid;
    }


    public function getOwnerIds($id)
    {
        $listModel = $this->listsListModelFactory->create()->load($id);
        $ownerId = $listModel->getOwnerId();
        return $ownerId;
    }
    /**
     * Retrives Websites from the list
     *
     * @return array $items
     */
    public function getWebsites()
    {
        $cacheKey = self::KEY_WEBSITES;
        if ($cache = $this->_getCachedData($cacheKey)) {
            return $cache;
        }

        /* @var $siteIdCollection \Epicor\Lists\Model\ResourceModel\ListModel\Website\Collection */
        $siteIdCollection = $this->listsResourceListModelWebsiteCollectionFactory->create();
        $siteIdCollection->addFieldToFilter('list_id', $this->getId());
        $websiteIds = $siteIdCollection->getColumnValues('website_id');

        $items = [];

        if ($websiteIds) {
            //M1 > M2 Translation Begin (Rule p2-1)
            //$collection = Mage::getModel('core/website');
            $collection = $this->websiteCollectionFactory->create();
            //M1 > M2 Translation End
            /* @var $collection \Magento\Store\Model\ResourceModel\Website\Collection */
            $collection->addFieldToFilter('website_id', $websiteIds);
            foreach ($collection->getItems() as $item) {
                $items[$item->getId()] = $item;
            }
        }

        $this->_cacheData($cacheKey, $items);

        return $items;
    }

    /**
     * Validates if the website exists in the list
     *
     * @param int $websiteId
     * @return bool
     */
    public function isValidForWebsite($websiteId)
    {
        $websites = $this->getWebsites();

        return isset($websites[$websiteId]) || count($websites) == 0;
    }

    /**
     * Retrives Stores from the list
     *
     * @return array $items
     */
    public function getStoreGroups()
    {
        $cacheKey = self::KEY_STORE_GROUPS;
        if ($cache = $this->_getCachedData($cacheKey)) {
            return $cache;
        }

        /* @var $storeIdCollection \Epicor\Lists\Model\ResourceModel\ListModel\Store\Group\Collection */
        $storeIdCollection = $this->listsResourceListModelStoreGroupCollectionFactory->create();
        $storeIdCollection->addFieldToFilter('list_id', $this->getId());
        $siteIds = $storeIdCollection->getColumnValues('store_group_id');

        $items = [];

        if ($siteIds) {
            //M1 > M2 Translation Begin (Rule p2-1)
            //$collection = Mage::getModel('core/store_group');
            $collection = $this->groupCollectionFactory->create();
            //M1 > M2 Translation End
            /* @var $collection \Magento\Store\Model\ResourceModel\Group\Collection */
            $collection->addFieldToFilter('group_id', $siteIds);
            foreach ($collection->getItems() as $item) {
                $items[$item->getId()] = $item;
            }
        }
        $this->_cacheData($cacheKey, $items);

        return $items;
    }

    /**
     * Validates if the store group exists in the list
     *
     * @param int $storeGroupId
     * @return bool
     */
    public function isValidForStoreGroup($storeGroupId)
    {
        $storeGroups = $this->getStoreGroups();

        return isset($storeGroups[$storeGroupId]) || count($storeGroups) == 0;
    }

    /**
     * Retrives Customers from the list
     *
     * @return array $items
     */
    public function getCustomers($id = false)
    {
        if ($id) {
            $this->setId($id);
        } else {
            $cacheKey = self::KEY_CUSTOMERS;
            if ($cache = $this->_getCachedData($cacheKey)) {
                return $cache;
            }
        }

        $collection = $this->customerResourceModelCustomerCollectionFactory->create();
        /* @var $collection Mage_Customer_Model_Resource_Customer_Collection */

        $collection->addAttributeToSelect('ecc_erp_account_type');
        $collection->addAttributeToSelect('ecc_erpaccount_id');
        $collection->getSelect()->join(
            array('list' => $collection->getTable('ecc_list_customer')), 'e.entity_id = list.customer_id AND list.list_id = "' . $this->getId() . '"', array()
        );

        $items = array();
        foreach ($collection->getItems() as $item) {
            $items[$item->getId()] = $item;
        }
        if (isset($cacheKey)) {
            $this->_cacheData($cacheKey, $items);
        }

        return $items;
    }

    /**
     * Gets the list of Customers, updated with changes
     *
     * @return array
     */
    public function getCustomersWithChanges()
    {
        $cacheKey = self::KEY_CUSTOMERS . '_UPDATED';
        if ($cache = $this->_getCachedData($cacheKey)) {
            return $cache;
        }

        $customers = $this->getCustomers();
        if (isset($this->_changes[self::KEY_CUSTOMERS])) {
            foreach ($this->_changes[self::KEY_CUSTOMERS] as $type => $items) {
                if ($type == self::ACTION_ADD) {
                    $collection = $this->customerResourceModelCustomerCollectionFactory->create();
                    /* @var $collection Mage_Customer_Model_Resource_Customer_Collection */
                    $collection->addAttributeToSelect('ecc_erp_account_type');
                    $collection->addAttributeToSelect('ecc_erpaccount_id');
                    $collection->addFieldToFilter('entity_id', array_keys($items));
                    $customers = $customers + $collection->getItems();
                } else if ($type == self::ACTION_REMOVE) {
                    foreach ($items as $key => $item) {
                        if (isset($customers[$key])) {
                            unset($customers[$key]);
                        }
                    }
                }
            }
        }

        $this->_cacheData($cacheKey, $customers);
        return $customers;
    }

    /**
     * Validates if the customer exists in the list
     *
     * @param \Epicor\Comm\Model\Customer $customer
     * @return bool
     */
    public function isValidForCustomer($customer)
    {
        $customers = $this->getCustomers();
        return isset($customers[$customer->getId()]) || count($customers) == 0;
    }

    /**
     * Retrives Addresses from the list
     *
     * @return array $items
     */
    public function getAddresses()
    {
        $cacheKey = self::KEY_ADDRESSES;
        if ($cache = $this->_getCachedData($cacheKey)) {
            return $cache;
        }

        $collection = $this->listsResourceListModelAddressCollectionFactory->create();
        /* @var $collection Epicor_Lists_Model_Resource_ListModel_Address_Collection */
        $collection->addFieldtoFilter('list_id', $this->getId());

        $items = array();
        foreach ($collection->getItems() as $item) {
            $items[$item->getId()] = $item;
        }

        $this->_cacheData($cacheKey, $items);

        return $items;
    }

    /**
     * Retrives Brands from the list
     *
     * @return array $items
     */
    public function getBrands()
    {
        $cacheKey = self::KEY_BRANDS;
        if ($cache = $this->_getCachedData($cacheKey)) {
            return $cache;
        }

        $collection = $this->listsResourceListModelBrandCollectionFactory->create();
        /* @var $collection Epicor_Lists_Model_Resource_ListModel_Brand_Collection */
        $collection->addFieldtoFilter('list_id', $this->getId());

        $items = array();
        foreach ($collection->getItems() as $item) {
            $items[$item->getId()] = $item;
        }

        $this->_cacheData($cacheKey, $items);

        return $items;
    }

    public function addBrands($brands)
    {
        if (!is_array($brands)) {
            $brands = array($brands);
        }

        foreach ($brands as $brand) {
            if ($brand instanceof \Magento\Framework\DataObject) {
                $this->_changes[self::KEY_BRANDS][self::ACTION_ADD][] = $brand;
            }
        }

        return $this;
    }

    public function removeBrands($brands)
    {
        if (!is_array($brands)) {
            $brands = array($brands);
        }

        foreach ($brands as $brand) {
            if ($brand instanceof \Magento\Framework\DataObject) {
                $this->_changes[self::KEY_BRANDS][self::ACTION_REMOVE][] = $brand;
            }
        }

        return $this;
    }

    public function addRestrictions($restrictions)
    {
        $this->_changes($restrictions, self::KEY_RESTRICTIONS, self::ACTION_ADD, 'restriction_type');
    }

    public function addAddresses($addresses)
    {
        $this->_changes($addresses, self::KEY_ADDRESSES, self::ACTION_ADD, 'address_code');
    }

    public function addPostcodes($postcodes)
    {
        $this->_changes($postcodes, self::KEY_ADDRESSES, self::ACTION_ADD, 'postcode');
    }

    public function addCounties($counties)
    {
        $this->_changes($counties, self::KEY_ADDRESSES, self::ACTION_ADD, 'county');
    }

    public function addCountries($countries)
    {
        $this->_changes($countries, self::KEY_ADDRESSES, self::ACTION_ADD, 'country');
    }

    public function removeAddresses($addresses)
    {
        $this->_changes($addresses, self::KEY_ADDRESSES, self::ACTION_REMOVE, 'address_code');
    }

    /**
     * Adds products pricing to the list
     *
     * @param array $pricing
     */
    public function addPricing($pricing)
    {

        if (isset($this->_changes[self::KEY_PRICING]) && is_array($pricing)) {
            $this->_changes[self::KEY_PRICING] = array_merge($this->_changes[self::KEY_PRICING], $pricing);
        } else {
            $this->_changes[self::KEY_PRICING] = $pricing;
        }

        return $this;
    }

    /**
     * Adds products to the list
     *
     * @param array|int|object $products
     */
    public function addProducts($products, $pricing = NULL)
    {
        $this->_changes($products, self::KEY_PRODUCTS, self::ACTION_ADD, 'sku');
         if ($pricing) {
            $this->addPricing($pricing);
        }
    }

    /**
     * Removes products from the list
     *
     * @param array|int|object $products
     */
    public function removeProducts($products)
    {
        $this->_changes($products, self::KEY_PRODUCTS, self::ACTION_REMOVE, 'sku');
    }

    public function setProductPrices($productCode, $prices)
    {
        if (!is_array($prices)) {
            $prices = array($prices);
        }
        $this->addProducts($productCode);

        $this->_pricing[$productCode] = $prices;
    }

    /**
     * Adds customers to the list
     *
     * @param array|int|object $customers
     */
    public function addCustomers($customers)
    {
        $this->_changes($customers, self::KEY_CUSTOMERS, self::ACTION_ADD);
    }

    /**
     * Removes customers from the list
     *
     * @param array|int|object $customers
     */
    public function removeCustomers($customers)
    {
        $this->_changes($customers, self::KEY_CUSTOMERS, self::ACTION_REMOVE);
    }

    /**
     * Adds websites to the list
     *
     * @param array|int|object $websites
     */
    public function addWebsites($websites)
    {
        $this->_changes($websites, self::KEY_WEBSITES, self::ACTION_ADD);
    }

    /**
     * Removes websites from the list
     *
     * @param array|int|object $websites
     */
    public function removeWebsites($websites)
    {
        $this->_changes($websites, self::KEY_WEBSITES, self::ACTION_REMOVE);
    }

    /**
     * Adds stores to the list
     *
     * @param array|int|object $storeGroups
     */
    public function addStoreGroups($storeGroups)
    {
        $this->_changes($storeGroups, self::KEY_STORE_GROUPS, self::ACTION_ADD);
    }

    /**
     * Removes store groups from the list
     *
     * @param array|int|object $storeGroups
     */
    public function removeStoreGroups($storeGroups)
    {
        $this->_changes($storeGroups, self::KEY_STORE_GROUPS, self::ACTION_REMOVE);
    }

    /**
     * Adds erp accounts to the list
     *
     * @param array|int|object $erpAccounts
     */
    public function addErpAccounts($erpAccounts)
    {
        $this->_changes($erpAccounts, self::KEY_ERP_ACCOUNTS, self::ACTION_ADD);
    }

    /**
     * Removes erp accounts from the list
     *
     * @param array|int|object $erpAccounts
     */
    public function removeErpAccounts($erpAccounts)
    {
        $this->_changes($erpAccounts, self::KEY_ERP_ACCOUNTS, self::ACTION_REMOVE);
    }

    /**
     * Returns the value for noCache
     *
     * @param bool
     */
    public function getNoCache()
    {
        return $this->_noCache;
    }

    /**
     * Sets the value for noCache
     *
     * @param bool
     */
    public function setNoCache($noCache = true)
    {
        $this->_noCache = $noCache;
    }

    /**
     * Clears cache for an specific key if given or all if not
     *
     * @param string $key
     */
    public function clearCache($key = null)
    {
        if ($key) {
            unset($this->_cache[$key]);
        } else {
            $this->_cache = array();
        }
    }

    /**
     * Returns cached data if avaible, else returns false
     *
     * @param string $key
     * @return mixed
     */
    protected function _getCachedData($key)
    {
        if (!$this->getNoCache() && isset($this->_cache[$key])) {
            return $this->_cache[$key];
        }

        return false;
    }

    /**
     * Caches data with given key
     *
     * @param string $key
     * @param mixed $data
     */
    protected function _cacheData($key, $data)
    {
        $this->_cache[$key] = $data;
    }

    /**
     * Registers label changes
     *
     * @param array|object|int $items
     * @param string $section
     * @param string $action
     */
    protected function _labelChanges($items, $section, $action)
    {
        if (!is_array($items)) {
            $items = array($items);
        }

        foreach ($items as $item) {
            $this->_changes[$section][$action][] = $item;
        }

        $this->_hasDataChanges = true;
    }

    /**
     * Registers changes
     *
     * @param array|object|int $items
     * @param string $section
     * @param string $action
     */
    protected function _changes($items, $section, $action, $idField = false)
    {
        $verify = ($action == self::ACTION_ADD ? self::ACTION_REMOVE : self::ACTION_ADD);

        if (!is_array($items)) {
            $items = array($items);
        }
        foreach ($items as $item) {
            $itemId = (is_object($item) ? ($idField ? $item->getData($idField) : $item->getId()) : $item);
            $this->_changes[$section][$action][$itemId] = $item;
            if (isset($this->_changes[$section][$verify][$itemId])) {
                unset($this->_changes[$section][$verify][$itemId]);
            }
        }
        $this->_hasDataChanges = true;
    }

    protected function _saveContract()
    {
        if ($this->getType() == 'Co' && $this->_contract instanceof \Epicor\Lists\Model\Contract) {
            if ($this->_contract->isObjectNew()) {
                $this->_contract->setListId($this->getId());
            }
            $this->_contract->save();
        }

        return $this;
    }

    protected function _saveBrands()
    {
        if (isset($this->_changes[self::KEY_BRANDS])) {
            if (isset($this->_changes[self::KEY_BRANDS][self::ACTION_REMOVE]) && is_array($this->_changes[self::KEY_BRANDS][self::ACTION_REMOVE])) {
                foreach ($this->_changes[self::KEY_BRANDS][self::ACTION_REMOVE] as $brand) {
                    if ($brand->getId()) {
                        $brand->delete();
                    } else {
                        foreach ($this->getBrands() as $matchBrand) {
                            if ($matchBrand->getCompany() == $brand->getCompany() &&
                                $matchBrand->getSite() == $brand->getSite() &&
                                $matchBrand->getWarehouse() == $brand->getWarehouse() &&
                                $matchBrand->getGroup() == $brand->getGroup()) {
                                $matchBrand->delete();
                            }
                        }
                    }
                }
            }

            if (isset($this->_changes[self::KEY_BRANDS][self::ACTION_ADD]) && is_array($this->_changes[self::KEY_BRANDS][self::ACTION_ADD])) {
                foreach ($this->_changes[self::KEY_BRANDS][self::ACTION_ADD] as $brand) {
                    $newBrand = $this->listsListModelBrandFactory->create();
                    /* @var $product Epicor_Lists_Model_ListModel_Brand */
                    $newBrand->setData($brand->getData());
                    $newBrand->setListId($this->getId());
                    $newBrand->save();
                }
            }
        }
    }

    protected function _saveAddresses()
    {

        if (isset($this->_changes[self::KEY_ADDRESSES])) {
            $addresses = array();
            foreach ($this->getAddresses() as $address) {
                $addresses[$address->getAddressCode()] = $address;
            }

            if (isset($this->_changes[self::KEY_ADDRESSES][self::ACTION_REMOVE]) && is_array($this->_changes[self::KEY_ADDRESSES][self::ACTION_REMOVE])) {
                foreach ($this->_changes[self::KEY_ADDRESSES][self::ACTION_REMOVE] as $addressCode => $address) {
                    if (isset($addresses[$addressCode])) {
                        $addresses[$addressCode]->delete();
                        unset($addresses[$addressCode]);
                    }
                }
            }

            if (isset($this->_changes[self::KEY_ADDRESSES][self::ACTION_ADD]) && is_array($this->_changes[self::KEY_ADDRESSES][self::ACTION_ADD])) {
                foreach ($this->_changes[self::KEY_ADDRESSES][self::ACTION_ADD] as $addressCode => $address) {
                    if (isset($addresses[$addressCode])) {
                        $newAddress = $addresses[$addressCode];
                    } else {
                        $newAddress = $this->listsListModelAddressFactory->create();
                        /* @var $newAddress Epicor_Lists_Model_ListModel_Address */
                        $newAddress->setListId($this->getId());
                    }
                    $newAddress->addData($address->getData());
                    $restrictedType = $this->registry->registry('restrictionType');
                    if(!is_null($restrictedType)){
                        $newAddress->setRestrictionType($restrictedType);
                    }
                    $newAddress->save();
                }
                $this->registry->unregister('restrictionType');
            }
        }
    }

    protected function _saveProducts()
    {
        if (isset($this->_changes[self::KEY_PRODUCTS])) {

            if (isset($this->_changes[self::KEY_PRODUCTS][self::ACTION_ADD]) && is_array($this->_changes[self::KEY_PRODUCTS][self::ACTION_ADD])) {
                $existingAddProducts = $this->getProducts(null, false) ? : [];
                //Check for the grouped products (Getting all the skus)
                $keySku = $this->_changes[self::KEY_PRODUCTS][self::ACTION_ADD];
                ///Check for the grouped products by SKUS
                $mergedSku = $this->saveGroupedProducts($keySku, []);
                if (!empty($mergedSku)) {
                    foreach ($mergedSku as $skuVals) {
                        $skuSplit[$skuVals] = $skuVals;
                    }
                    $emptyElement = array_filter($skuSplit);
                    //if found, merge the sku's with the existing SKU array
                    $this->_changes[self::KEY_PRODUCTS][self::ACTION_ADD] = $this->_changes[self::KEY_PRODUCTS][self::ACTION_ADD] + $emptyElement;
                }

                foreach ($this->_changes[self::KEY_PRODUCTS][self::ACTION_ADD] as $sku => $objProduct) {
                    if (!array_key_exists($sku, $existingAddProducts)) {
                        $product = $this->listsListModelProductFactory->create();
                        /* @var $product \Epicor\Lists\Model\ListModel\Product */
                        $product->setSku($sku);
                        $product->setListId($this->getId());
                        if ($objProduct instanceof \Magento\Framework\DataObject) {
                            $product->setQty($objProduct->getQty());
                        }

                        if (isset($this->_changes[self::KEY_PRICING][$sku])) {
                            $product->setPricing($this->_changes[self::KEY_PRICING][$sku]);
                        }
                        $product->save();
                    } elseif (isset($this->_changes[self::KEY_PRICING][$sku])) {
                        $product = $this->listsResourceListModelProductCollectionFactory->create()
                            ->addFieldToFilter('list_id', $this->getId())
                            ->addFieldToFilter('sku', $sku)
                            ->getFirstItem();
                        if (!$product->isObjectNew()) {
                            $product->setPricing($this->_changes[self::KEY_PRICING][$sku]);
                            $product->save();
                        }
                    }
                }
            }

            if (isset($this->_changes[self::KEY_PRODUCTS][self::ACTION_REMOVE]) && is_array($this->_changes[self::KEY_PRODUCTS][self::ACTION_REMOVE])) {
                $existingProducts = $this->getProducts(null, false);
                $skus = array();
                foreach ($this->_changes[self::KEY_PRODUCTS][self::ACTION_REMOVE] as $sku => $product) {
                    if (array_key_exists($sku, $existingProducts)) {
                        $skus[] = $sku;
                    }
                }

                if (count($skus) > 0) {
                    //check if there any associated grouped products found
                    $getSkus = $this->removeGroupedProducts($skus, array_keys($existingProducts));
                    if (!empty($getSkus)) {
                        //if found, merge the sku's with the existing SKU array
                        $skus = array_merge($skus, $getSkus);
                    }

                    $productsCollection = $this->listsResourceListModelProductCollectionFactory->create();
                    /* @var $productsCollection Epicor_Lists_Model_Resource_ListModel_Product_Collection */
                    $productsCollection->addFieldtoFilter('list_id', $this->getId());
                    $productsCollection->addFieldtoFilter('sku', array('in' => $skus));

                    foreach ($productsCollection->getItems() as $item) {
                        $item->delete();
                    }
                }
            }
        }

        $this->_savePricing();
    }

    /*  Save groupped products
     */

    public function saveGroupedProducts($skus, $existingAddProducts = [], $locationIncluded = false, $getParent = false)
    {
        if($locationIncluded){
            $getKeys = array_keys($skus);
            $getKeys = array_map(function($key){
                $keyArr =  explode('_', $key);
                array_pop($keyArr);
                $strKey = implode('_', $keyArr);
                return $strKey;
            }, $getKeys);
        }else{
            $getKeys = array_keys($skus);
        }
        if (!empty($getKeys)) {
            $typeId = '';
            $getKeys = array_map('strval', $getKeys);
            $adapter = $this->resourceConnection;
            $tableProducts = $adapter->getTableName('catalog_product_entity');
            $tableRelations = $adapter->getTableName('catalog_product_relation');
            $read = $adapter->getConnection('read');
            if((in_array($this->getType(), ['Co', 'Pg']) && in_array($this->request->getActionName(), [ 'upload', 'massReprocess', 'reprocess'])) || $this->request->getActionName() == 'csvupload' || $getParent){
                $select = $read->select()->from(array('main_table' => $tableProducts), array('relation.parent_id'))
                    ->joinLeft(array('relation' => $tableRelations), 'relation.child_id = main_table.entity_id', null)
                    ->where('main_table.sku IN(?)', array($getKeys))
                    ->where('relation.parent_id != ?', '')
                    ->group('relation.parent_id');
                $productsIds = $read->fetchCol($select);
                $typeId = 'grouped';
            }else{
                $select = $read->select()->from(array('main_table' => $tableProducts), array('relation.child_id', 'main_table.sku'))
                    ->joinLeft(array('relation' => $tableRelations), 'relation.parent_id = main_table.entity_id', null)
                    ->where('main_table.sku IN(?)', array($getKeys))
                    ->where('main_table.type_id NOT LIKE ?', Type::TYPE_CODE)
                    ->where('relation.child_id != ?', '')
                    ->group('relation.child_id');
                $productsIds = $read->fetchCol($select);
                if(!empty($existingAddProducts)){
                    $productsIds = array_diff_key(array_flip($productsIds), array_intersect($read->fetchPairs($select), array_keys($existingAddProducts)));
                    $productsIds = array_flip($productsIds);
                }
            }

            if (!empty($productsIds)) {
                $productsCollection = $this->catalogResourceModelProductCollectionFactory->create()
                    ->addAttributeToSelect('sku')
                    ->addAttributeToFilter('entity_id', array('in' => $productsIds))
                    ->setFlag('no_product_filtering', true);
                if ($typeId){
                    $productsCollection->addAttributeToFilter('type_id', array('eq' => $typeId));
                }
                 $products = $productsCollection->getColumnValues('sku');
                 return $products;
            }

        }
    }

    /*  Remove grouped products
     */

    public function removeGroupedProducts($skus, $existingProducts)
    {
        if (!empty($skus)) {
            $adapter = $this->resourceConnection;
            $tableProducts = $adapter->getTableName('catalog_product_entity');
            $tableRelations = $adapter->getTableName('catalog_product_relation');
            $read = $adapter->getConnection('read');
            $implode = implode(',', $skus);
            if((in_array($this->getType(), ['Co', 'Pg']) && $this->request->getActionName() === 'upload') || $this->request->getActionName() == 'csvupload'){
             return $this->removeParent($read, $tableProducts, $tableRelations, $skus, $existingProducts);
            }else{
                $select = $read->select()->from(array('main_table' => $tableProducts), array('relation.child_id', 'relation.parent_id'))
                    ->joinLeft(array('relation' => $tableRelations), 'relation.parent_id = main_table.entity_id', null)
                    ->where('main_table.sku IN(?)', array($skus))
                    ->where('main_table.type_id NOT LIKE ?', Type::TYPE_CODE)
                    ->where('relation.child_id != ?', '');
                $removeIds = $read->fetchPairs($select);
                $mergedIds = array_keys($removeIds);
                $mergedIds = !empty($removeIds) ? array_merge($mergedIds, [array_pop($removeIds)]) : [];

                if (!empty($mergedIds)) {
                    $productsCollection = $this->catalogResourceModelProductCollectionFactory->create()
                        ->addAttributeToFilter('entity_id', array('in' => $mergedIds));
                    return $products = $productsCollection->getColumnValues('sku');
                }else{
                    return $this->removeParent($read, $tableProducts, $tableRelations, $skus, $existingProducts);
                }
            }
        }
    }

    /*  Finding differences in two multidimensional arrays
     */

    public function array_diff_assoc_recursive($array1, $array2)
    {
        foreach ($array1 as $key => $value) {
            if (is_array($value)) {
                if (!isset($array2[$key])) {
                    $difference[$key] = $value;
                } elseif (!is_array($array2[$key])) {
                    $difference[$key] = $value;
                } else {
                    $new_diff = array_diff_assoc_recursive($value, $array2[$key]);
                    if ($new_diff != FALSE) {
                        $difference[$key] = $new_diff;
                    }
                }
            } elseif (!isset($array2[$key]) || $array2[$key] != $value) {
                $difference[$key] = $value;
            }
        }
        return !isset($difference) ? 0 : $difference;
    }

    /*  Check child products exists, while removing the products
     */

    public function checkChildExists($skus)
    {

        $adapter = $this->resourceConnection;
        $tableProducts = $adapter->getTableName('catalog_product_entity');
        $tableRelations = $adapter->getTableName('catalog_product_relation');
        $read = $adapter->getConnection('read');
        $select = $read->select()->from(array('main_table' => $tableProducts), array('relation.parent_id'))
            ->joinLeft(array('relation' => $tableRelations), 'relation.child_id = main_table.entity_id', null)
            ->where('main_table.sku  IN(?)', $skus)
            ->where('relation.parent_id != ?', '');
        return $productsIds = $read->fetchCol($select);
    }

    protected function _savePricing()
    {
        if (count($this->_pricing) == 0) {
            return $this;
        }

        $productsCollection = $this->listsResourceListModelProductCollectionFactory->create();
        /* @var $productsCollection Epicor_Lists_Model_Resource_ListModel_Product_Collection */
        $productsCollection->addFieldtoFilter('list_id', $this->getId());

        $products = array();
        foreach ($productsCollection->getItems() as $item) {
            $products[$item->getSku()] = $item;
        }

        foreach ($this->_pricing as $productCode => $productPrices) {
            if (isset($products[$productCode])) {
                $product = $products[$productCode];
                /* @var $product Epicor_Lists_Model_ListModel_Product */
                $product->setPricing($productPrices);
                $product->save();
            }
        }
    }

    protected function _saveCustomers()
    {
        if (isset($this->_changes[self::KEY_CUSTOMERS])) {
            $existingCustomers = $this->getCustomers();

            if (isset($this->_changes[self::KEY_CUSTOMERS][self::ACTION_ADD]) && is_array($this->_changes[self::KEY_CUSTOMERS][self::ACTION_ADD])) {
                foreach ($this->_changes[self::KEY_CUSTOMERS][self::ACTION_ADD] as $customerId => $customer) {
                    if (!array_key_exists($customerId, $existingCustomers)) {
                        $customer = $this->listsListModelCustomerFactory->create();
                        /* @var $customer Epicor_Lists_Model_ListModel_Customer */
                        $customer->setCustomerId($customerId);
                        $customer->setListId($this->getId());

                        $customer->save();
                    }
                }

                unset($this->_changes[self::KEY_CUSTOMERS][self::ACTION_ADD]);
            }

            if (isset($this->_changes[self::KEY_CUSTOMERS][self::ACTION_REMOVE]) && is_array($this->_changes[self::KEY_CUSTOMERS][self::ACTION_REMOVE])) {
                $customerIds = array();
                foreach ($this->_changes[self::KEY_CUSTOMERS][self::ACTION_REMOVE] as $customerId => $customer) {
                    if (array_key_exists($customerId, $existingCustomers)) {
                        $customerIds[] = $customerId;
                    }
                }

                if (count($customerIds) > 0) {
                    $customersCollection = $this->listsResourceListModelCustomerCollectionFactory->create();
                    /* @var $customersCollection Epicor_Lists_Model_Resource_ListModel_Customer_Collection */
                    $customersCollection->addFieldtoFilter('list_id', $this->getId());
                    $customersCollection->addFieldtoFilter('customer_id', array('in' => $customerIds));

                    foreach ($customersCollection->getItems() as $item) {
                        $item->delete();
                    }
                }

                unset($this->_changes[self::KEY_CUSTOMERS][self::ACTION_REMOVE]);
            }
        }
    }

    protected function _saveErpAccounts()
    {
        if (isset($this->_changes[self::KEY_ERP_ACCOUNTS])) {
            $existingErpAccounts = $this->getErpAccounts();

            if (isset($this->_changes[self::KEY_ERP_ACCOUNTS][self::ACTION_ADD]) && is_array($this->_changes[self::KEY_ERP_ACCOUNTS][self::ACTION_ADD])) {
                foreach ($this->_changes[self::KEY_ERP_ACCOUNTS][self::ACTION_ADD] as $erpAccountId => $erpAccount) {
                    if (!array_key_exists($erpAccountId, $existingErpAccounts)) {
                        $erpAccount = $this->listsListModelErpAccountFactory->create();
                        $erpAccount = $this->listsListModelErpAccountFactory->create();
                        /* @var $erpAccount Epicor_Lists_Model_ListModel_Erp_Account */
                        $erpAccount->setErpAccountId($erpAccountId);
                        $erpAccount->setListId($this->getId());

                        $erpAccount->save();
                    }
                    $getAllIds[] = $erpAccountId;
                }

                unset($this->_changes[self::KEY_ERP_ACCOUNTS][self::ACTION_ADD]);
            }

            if (isset($this->_changes[self::KEY_ERP_ACCOUNTS][self::ACTION_REMOVE]) && is_array($this->_changes[self::KEY_ERP_ACCOUNTS][self::ACTION_REMOVE])) {
                $erpAccountIds = array();
                foreach ($this->_changes[self::KEY_ERP_ACCOUNTS][self::ACTION_REMOVE] as $erpAccountId => $erpAccount) {
                    if (array_key_exists($erpAccountId, $existingErpAccounts)) {
                        $erpAccountIds[] = $erpAccountId;
                    }
                }

                if (count($erpAccountIds) > 0) {
                    $erpAccountsCollection = $this->listsResourceListModelErpAccountCollectionFactory->create();
                    /* @var $erpAccountsCollection Epicor_Lists_Model_Resource_ListModel_Erp_Account_Collection */
                    $erpAccountsCollection->addFieldtoFilter('list_id', $this->getId());
                    $erpAccountsCollection->addFieldtoFilter('erp_account_id', array('in' => $erpAccountIds));

                    foreach ($erpAccountsCollection->getItems() as $item) {
                        $item->delete();
                    }
                }

                unset($this->_changes[self::KEY_ERP_ACCOUNTS][self::ACTION_REMOVE]);
            }
        }
    }


    protected function _saveWebsites()
    {
        if (isset($this->_changes[self::KEY_WEBSITES])) {
            $existingWebsites = $this->getWebsites();

            if (isset($this->_changes[self::KEY_WEBSITES][self::ACTION_ADD]) && is_array($this->_changes[self::KEY_WEBSITES][self::ACTION_ADD])) {
                foreach ($this->_changes[self::KEY_WEBSITES][self::ACTION_ADD] as $websiteId => $website) {
                    if (!array_key_exists($websiteId, $existingWebsites)) {
                        $website = $this->listsListModelWebsiteFactory->create();
                        /* @var $store Epicor_Lists_Model_ListModel_Website */
                        $website->setWebsiteId($websiteId);
                        $website->setListId($this->getId());

                        $website->save();
                    }
                }

                unset($this->_changes[self::KEY_WEBSITES][self::ACTION_ADD]);
            }

            if (isset($this->_changes[self::KEY_WEBSITES][self::ACTION_REMOVE]) && is_array($this->_changes[self::KEY_WEBSITES][self::ACTION_REMOVE])) {
                $websiteIds = array();
                foreach ($this->_changes[self::KEY_WEBSITES][self::ACTION_REMOVE] as $websiteId => $website) {
                    if (array_key_exists($websiteId, $existingWebsites)) {
                        $websiteIds[] = $websiteId;
                    }
                }

                if (count($websiteIds) > 0) {
                    $websitesCollection = $this->listsResourceListModelWebsiteCollectionFactory->create();
                    /* @var $websitesCollection Epicor_Lists_Model_Resource_ListModel_Website_Collection */
                    $websitesCollection->addFieldtoFilter('list_id', $this->getId());
                    $websitesCollection->addFieldtoFilter('website_id', array('in' => $websiteIds));

                    foreach ($websitesCollection->getItems() as $item) {
                        $item->delete();
                    }
                }

                unset($this->_changes[self::KEY_WEBSITES][self::ACTION_REMOVE]);
            }
        }
    }

    protected function _saveStoreGroups()
    {
        if (isset($this->_changes[self::KEY_STORE_GROUPS])) {
            $existingStoreGroups = $this->getStoreGroups();

            if (isset($this->_changes[self::KEY_STORE_GROUPS][self::ACTION_ADD]) && is_array($this->_changes[self::KEY_STORE_GROUPS][self::ACTION_ADD])) {
                foreach ($this->_changes[self::KEY_STORE_GROUPS][self::ACTION_ADD] as $storeGroupId => $storeGroup) {
                    if (!array_key_exists($storeGroupId, $existingStoreGroups)) {
                        $storeGroup = $this->listsListModelStoreGroupFactory->create();
                        /* @var $store Epicor_Lists_Model_ListModel_Store_Group */
                        $storeGroup->setStoreGroupId($storeGroupId);
                        $storeGroup->setListId($this->getId());

                        $storeGroup->save();
                    }
                }

                unset($this->_changes[self::KEY_STORE_GROUPS][self::ACTION_ADD]);
            }

            if (isset($this->_changes[self::KEY_STORE_GROUPS][self::ACTION_REMOVE]) && is_array($this->_changes[self::KEY_STORE_GROUPS][self::ACTION_REMOVE])) {
                $storeGroupIds = array();
                foreach ($this->_changes[self::KEY_STORE_GROUPS][self::ACTION_REMOVE] as $storeGroupId => $storeGroup) {
                    if (array_key_exists($storeGroupId, $existingStoreGroups)) {
                        $storeGroupIds[] = $storeGroupId;
                    }
                }

                if (count($storeGroupIds) > 0) {
                    $storeGroupsCollection = $this->listsResourceListModelStoreGroupCollectionFactory->create();
                    /* @var $storeGroupsCollection Epicor_Lists_Model_Resource_ListModel_Store_Group_Collection */
                    $storeGroupsCollection->addFieldtoFilter('list_id', $this->getId());
                    $storeGroupsCollection->addFieldtoFilter('store_group_id', array('in' => $storeGroupIds));

                    foreach ($storeGroupsCollection->getItems() as $item) {
                        $item->delete();
                    }
                }

                unset($this->_changes[self::KEY_STORE_GROUPS][self::ACTION_REMOVE]);
            }
        }
    }

    /**
     * Saves Label changes
     */
    protected function _saveLabels()
    {
        $key = self::KEY_LABELS;
        if (isset($this->_changes[$key])) {
            $labels = $this->getLabels();

            if (isset($this->_changes[$key][self::ACTION_ADD]) && is_array($this->_changes[$key][self::ACTION_ADD])) {
                foreach ($this->_changes[$key][self::ACTION_ADD] as $label) {
                    /* @var $label Epicor_Lists_Model_ListModel_Label */
                    $label->save();
                }

                unset($this->_changes[$key][self::ACTION_ADD]);
            }

            if (isset($this->_changes[$key][self::ACTION_UPDATE]) && is_array($this->_changes[$key][self::ACTION_UPDATE])) {
                foreach ($this->_changes[$key][self::ACTION_UPDATE] as $label) {
                    $label->save();
                }

                unset($this->_changes[$key][self::ACTION_UPDATE]);
            }

            if (isset($this->_changes[$key][self::ACTION_REMOVE]) && is_array($this->_changes[$key][self::ACTION_REMOVE])) {
                foreach ($this->_changes[$key][self::ACTION_REMOVE] as $labelId) {
                    if (isset($labels[$labelId])) {
                        $label = $labels[$labelId];
                        $label->delete();
                    }
                }

                unset($this->_changes[$key][self::ACTION_REMOVE]);
            }
        }
    }

    /**
     * Returns whether the list is active
     *
     * @return boolean
     */
    public function isActive()
    {
        if ($this->getActive() == 0) {
            return false;
        }


        $startDate = $this->getStartDate();
        $endDate = $this->getEndDate();

        $dateModel = $this->dateTimeDateTimeFactory->create();
        $currentTimeStamp = $dateModel->timestamp(time());
        $startTimeStamp = $dateModel->timestamp(strtotime($startDate));
        $endTimeStamp = $dateModel->timestamp(strtotime($endDate));

        if ($endDate && $endTimeStamp < $currentTimeStamp) {
            return false;
        } else if ($startDate && $startTimeStamp > $currentTimeStamp) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Gets the typeinstance for this List
     *
     * @return \Epicor\Lists\Model\ListModel\Type\AbstractModel
     */
    public function getTypeInstance()
    {
        if (is_null($this->typeInstance)) {
            $typeModel = $this->listsListModelTypeFactory->create();
            /* @var $typeModel Epicor_Lists_Model_ListModel_Type */
            $instance = $typeModel->getTypeInstanceValue($this->getType());
            if ($instance) {
                /* To fix dynamic loading of corresponding model instance */
                $instance = ucfirst($instance);
                $modelFactory = "listsListModelType{$instance}Factory";
                $this->typeInstance = $this->$modelFactory->create();
            } else {
                $this->typeInstance = $this->listsListModelTypeAbstractFactory->create();
            }

            $this->typeInstance->setData($this->getData());
        }

        return $this->typeInstance;
    }

    public function validate($frontEnd = null)
    {
        $errors = array();

        $erpCode = $this->getErpCode();
        if (empty($erpCode)) {
            $errors[] = __('List Code must not be empty');
        }

        $title = $this->getTitle();
        if (empty($title)) {
            $errors[] = __('Title must not be empty');
        }

        $type = $this->getType();
        if (empty($type)) {
            $errors[] = __('Type must not be empty');
        }

        if ($this->isObjectNew()) {
            if (empty($erpCode) == false) {
                $listColl = $this->listsResourceListModelCollectionFactory->create();
                /* @var $listColl Epicor_Lists_Model_Resource_ListModel_Collection */
                $listColl->addFieldToFilter('erp_code', $erpCode);
                if ($listColl->count() > 0) {
                    $errors[] = !empty($frontEnd) ? __('List Code "' . $erpCode . '"" is already taken by another list. Please enter a different  code.') : __('List Code must be unique');
                }
            }
        }


        //check if excluded erp account indicator set and no erpaccounts selected (should only be triggered for backend)
        if ($this->request->getParam('selected_erpaccounts')) {
            $linksOfAcctsSelected = $this->request->getParam('links');
            $accountsSelected = $linksOfAcctsSelected['erpaccounts'];
            if (!$accountsSelected && ($this->getErpAccountLinkType() != 'N') && ($this->getErpAccountsExclusion() == 'N')) {
                $errors[] = __("Cannot update list. Either one or more ERP Accounts must be selected for inclusion or 'Exclude Selected Erp Accounts' should be ticked");
            }
        }

        return empty($errors) ? true : $errors;
    }

    public function getTypeText()
    {
        $types = $this->listsListModelTypeFactory->create()->toFilterArray();
        $type = $this->getType();

        if (isset($types[$type])) {
            return $types[$type];
        }

        return false;
    }

    public function setJsFormObject($form)
    {
        $this->setData('js_form_object', $form);
        foreach ($this->getConditions() as $condition) {
            $condition->setJsFormObject($form);
        }
        return $this;
    }

    /**
     * Checks to see if orphans need to be removed
     *
     * @param string $flag
     * @return boolean|array
     */
    public function orphanCheck($flag = '')
    {
        $warn = ($flag == 'warn');
        // check link type has changed

        if (
            $this->getOrigData('erp_account_link_type') == $this->getErpAccountLinkType() &&
            $this->getOrigData('erp_accounts_exclusion') == $this->getErpAccountsExclusion() &&
            isset($this->_changes[self::KEY_ERP_ACCOUNTS]) == false
        ) {
            return false;
        }

        // check any ERP accounts to be removed
        $orphanErpAccounts = $this->removeOrphanErpAccounts($warn);
        $orphanCustomers = $this->removeOrphanCustomers($warn);

        if ($orphanErpAccounts == false && $orphanCustomers == false) {
            return false;
        }

        if ($warn) {
            $messageArray = array( __('Changes made to this List will result in the deletion of : ') );

            $orphanErpAccountsCount = 0;
            if (empty($orphanErpAccounts) == false) {
                $orphanErpAccountsCount = count($orphanErpAccounts);
                $messageArray[] = __('ERP Accounts : %1', $orphanErpAccountsCount);
            }

            $orphanCustomersCount = 0;
            if (empty($orphanCustomers) == false) {
                $orphanCustomersCount = count($orphanCustomers);
                $messageArray[] = __('Customers : %1', $orphanCustomersCount);
            }

            return array(
                'message' => implode("\n\n", $messageArray),
                'type' => 'success',
                'erpaccounts' => count($this->getErpAccountsWithChanges()) - $orphanErpAccountsCount
            );
        }

        return true;
    }

    /**
     * Looks at the ERP Accounts to determine if they need to be
     * @param boolean $warn
     * @return boolean|array
     */
    protected function removeOrphanErpAccounts($warn)
    {
        $erpAccounts = $this->getErpAccountsWithChanges();

        if (empty($erpAccounts)) {
            return false;
        }

        $removeType = $this->getAccountTypeToRemove();

        if ($removeType == 'None') {
            return false;
        }

        $removeErpAccounts = array();

        foreach ($erpAccounts as $key => $erpAccount) {
            /* @var $erpAccount Epicor_Comm_Model_Customer_Erpaccount */
            if (
                $erpAccount->getAccountType() == $removeType ||
                $removeType == 'All'
            ) {
                $removeErpAccounts[$key] = $erpAccount;
            }
        }

        if (empty($removeErpAccounts)) {
            return false;
        }

        if ($warn == false) {
            $this->removeErpAccounts($removeErpAccounts);
        }
        return $removeErpAccounts;
    }

    /**
     * Removes orphan customers
     * @param boolean $warn
     * @return boolean|array
     */
    protected function removeOrphanCustomers($warn)
    {
        $customers = $this->getCustomersWithChanges();

        if (empty($customers)) {
            return false;
        }

        $removeType = $this->getAccountTypeToRemove();
        $removeCustomers = array();
        foreach ($customers as $key => $customer) {
            /* @var $customer Epicor_Comm_Model_Customer */
            if ($this->shouldRemoveCustomer($customer, $removeType)) {
                $removeCustomers[$key] = $customer;
            }
        }

        if (empty($removeCustomers)) {
            return false;
        }

        if ($warn == false) {
            $this->removeCustomers($removeCustomers);
        }
        return $removeCustomers;
    }

    /**
     * Works out if a customer should be removed from this list due to data integrity
     *
     * @param \Epicor\Comm\Model\Customer $customer
     * @param string $removeType
     *
     * @return boolean
     */
    protected function shouldRemoveCustomer($customer, $removeType)
    {
        // sales reps should never be removed
        if ($customer->isSalesRep()) {
            return false;
        }

        // simple check: does the customer match the correct link type
        if (
            ($removeType == 'B2B' && $customer->isCustomer(false)) ||
            ($removeType == 'B2C' && $customer->isGuest(false))
        ) {
            return true;
        }

        $excluded = $this->getErpAccountsExclusion();
        $erpAccounts = $this->getErpAccountsWithChanges();
        $hasErpAccounts = (empty($erpAccounts) == false);

        // if B2C, exclusion set to no, then remove guests with no erp accounts
        if (
            $customer->isGuest(false) &&
            $customer->getEccErpaccountId() == false &&
            $this->getErpAccountLinkType() == self::ERP_ACC_LINK_TYPE_B2C &&
            $excluded == 'N' &&
            $hasErpAccounts
        ) {
            return true;
        }

        // if excluded, customer erp account should not be selected
        // if inclusion, customer erp account should be selected
        $accountSelected = isset($erpAccounts[$customer->getEccErpaccountId()]);
        if (
            $this->getErpAccountLinkType() != self::ERP_ACC_LINK_TYPE_NONE &&
            (($excluded == 'N' && $accountSelected == false) ||
            ($excluded == 'Y' && $accountSelected == true))
        ) {
            return true;
        }

        return false;
    }

    /**
     * Returns the type of terp accounts / customers to be removed basedon link type
     *
     * @return string
     */
    protected function getAccountTypeToRemove()
    {
        $linkType = $this->getErpAccountLinkType();
        switch ($linkType) {
            case self::ERP_ACC_LINK_TYPE_B2C:
                $removeType = 'B2B';
                break;
            case self::ERP_ACC_LINK_TYPE_B2B:
                $removeType = 'B2C';
                break;
            case self::ERP_ACC_LINK_TYPE_NONE:
                $removeType = 'All';
                break;
            default:
                $removeType = 'None';
                break;
        }

        return $removeType;
    }

    public function removeParent($read, $tableProducts, $tableRelations, $skus, $existingProducts)
    {
        $select = $read->select()->from(array('main_table' => $tableProducts), array('relation.parent_id'))
            ->joinLeft(array('relation' => $tableRelations), 'relation.child_id = main_table.entity_id', null)
            ->where('main_table.sku IN(?)', array($skus))
            ->where('relation.parent_id != ?', '');
        $productsIds = $read->fetchCol($select);

        $assignedIds = array_count_values($productsIds);
        //Get the parent Id's of the existing products
        $checkChildExists = $this->checkChildExists($existingProducts);
        //count the no of values in the array
        $countVals = array_count_values($checkChildExists);
        // Finding differences in two multidimensional arrays
        $diff = $this->array_diff_assoc_recursive($countVals, $assignedIds);

        //It will it keep the parent product if only one uom child is removed
        foreach ($assignedIds as $keyids => $keyv) {
            if ($diff == 0 || !array_key_exists($keyids, $diff)) {
                $removeId[] = $keyids;
            }
        }
        if (!empty($removeId)) {
            $productsCollection = $this->catalogResourceModelProductCollectionFactory->create()
                ->addAttributeToFilter('type_id', array('eq' => 'grouped'))
                ->addAttributeToFilter('entity_id', array('in' => $removeId));
            return $products = $productsCollection->getColumnValues('sku');
        }
    }

    /**
     * Remove ALl Products.
     *
     * @return int
     */
    public function removeAllProducts()
    {
        $listId = $this->getId();
        $table = $this->resourceConnection->getTableName("ecc_list_product");

        return $this->resourceConnection
            ->getConnection()
            ->delete($table,["list_id = $listId"]);

    }//end removeAllProducts()

    /**
     * Get Identities
     *
     * @return array|string[]
     */
    public function getIdentities()
    {
        return [$this->getType().'_'.$this->getId()];
    }
}
