<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Erp\Catalog;


/**
 * Catalog Category class for Erp
 * For functionality used by multiple different XML messages
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Category extends \Epicor\Database\Model\Erp\Catalog\Category\Entity
{

    private $groupLengthArr;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Erp\Catalog\Category\CollectionFactory
     */
    protected $commResourceErpCatalogCategoryCollectionFactory;

    /**
     * @var \Epicor\Comm\Model\Erp\Catalog\CategoryFactory
     */
    protected $commErpCatalogCategoryFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTimeDateTime;
    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    protected $catalogApiCategoryRepositoryInterface;
    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $catalogCategoryFactory;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Epicor\Comm\Model\ResourceModel\Erp\Catalog\Category\CollectionFactory $commResourceErpCatalogCategoryCollectionFactory,
        \Epicor\Comm\Model\Erp\Catalog\CategoryFactory $commErpCatalogCategoryFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTimeDateTime,
        \Magento\Catalog\Api\CategoryRepositoryInterface $catalogApiCategoryRepositoryInterface,
        \Magento\Catalog\Model\CategoryFactory $catalogCategoryFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,

        array $data = []
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->commResourceErpCatalogCategoryCollectionFactory = $commResourceErpCatalogCategoryCollectionFactory;
        $this->commErpCatalogCategoryFactory = $commErpCatalogCategoryFactory;
        $this->dateTimeDateTime = $dateTimeDateTime;
        $this->catalogApiCategoryRepositoryInterface = $catalogApiCategoryRepositoryInterface;
        $this->catalogCategoryFactory = $catalogCategoryFactory;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }


    protected function _construct()
    {
        // initialize resource model
        $this->_init('Epicor\Comm\Model\ResourceModel\Erp\Catalog\Category');

        // get definition of code structure from the config
        $groups = explode(',', $this->scopeConfig->getValue('Epicor_Comm/group_length/sub', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
        $arr = array();
        foreach ($groups as $group) {
            $temp = explode(':', $group);
            $key = $temp[0];
            $arr[$key] = $temp[1];
        }
        $this->groupLengthArr = $arr;
    }

    /**
     * Get group level based on code
     * 
     * @access private 
     * @param string $erpCode
     * @return integer
     */
    private function _getGroupLevel($erpCode)
    {

        if (!$erpCode) {
            return 0;
        }

        $l = strlen($erpCode);
        $group = 0;
        while ($l > 0 and ( $group <= count($this->groupLengthArr))) {
            $group++;
            $l -= $this->groupLengthArr[$group];
        }
        return($group);
    }

    /**
     * Get all groups based on code
     * 
     * @access public 
     * @param string $erpCode
     * @return array|boolean
     */
    public function getGroups($erpCode)
    {

        if (!$erpCode) {
            return false;
        }
        $length = strlen($erpCode);
        $groups = array();
        $level = 1;
        $matchLength = 0;
        // initial match length
        while ($length > 0 and ( $level < count($this->groupLengthArr))) {
            $length -= $matchLength;
            $matchLength += $this->groupLengthArr[$level];
            $groups[] = substr($erpCode, 0, $matchLength);
            $level++;
        }
        return $groups;
    }

    /**
     * Get ERP parent group code based on code
     * 
     * @access public
     * @param string $erpCode
     * @return string|boolean
     */
    public function getErpParentGroupCode($erpCode)
    {

        if (!$erpCode) {
            return false;
        }

        $parentErpCode = '';
        $level = $this->_getGroupLevel($erpCode) - 1;

        if (strlen($erpCode) > 0) {
            $len = 0;
            for ($i = 1; $i <= $level; $i++) {
                $len += $this->groupLengthArr[$i];
            }
            $parentErpCode = substr($erpCode, 0, $len);
        }
        return $parentErpCode;
    }

    /**
     * Create entire path in Magento tree structure for a given ERP code
     * 
     * @access public
     * @param string $erpCode
     * @param integer $storeId
     * @return integer
     */
    public function createPath($erpCode, $storeId)
    {
        // get all the groups of the erp code
        $groups = $this->getGroups($erpCode);

        if (!empty($groups)) {
            // set to root category of the store
            $groupId = $this->storeManager->getStore($storeId)->getRootCategoryId();

            foreach ($groups as $group) {
                // check if the group code is in the mapping table
                $groupColl = $this->commResourceErpCatalogCategoryCollectionFactory->create()
                    ->addFieldToFilter('erp_code', $group)
                    ->addFieldToSelect('magento_id')
                    ->setPageSize(1)
                ;

                if (!empty($groupColl) && (count($groupColl) > 0)) {
                    // group code found in the mapping table
                    // set it as new groupId
                    $groupId = $groupColl
                        ->getFirstItem()
                        ->getMagentoId()
                    ;
                } else {
                    // group code not found in the mapping table
                    // create it with some default values
                    //M1 > M2 Translation Begin (Rule 8)
                    $groupData = array(
                        'name' => $group,
                        'is_active' => false,
                        'available_sort_by' => false,
                        'default_sort_by' => false,
                    );

                    // get group level
                    $groupLevel = $this->_getGroupLevel($group);

                    if (!empty($groupLevel) && ($groupLevel > 3)) {
                        // don't show in menu above 3rd level
                        $groupData['include_in_menu'] = false;
                    } else {
                        // default
                        $groupData['include_in_menu'] = true;
                    }

                    // create in Magento
                    try {
                        //$groupId = Mage::getModel('catalog/category_api')
                        // ->create($groupId, $groupData, $storeId);
                        $category = $this->catalogCategoryFactory->create($groupData);
                        $repository = $this->catalogApiCategoryRepositoryInterface;
                        $result = $repository->save($category);
                        $groupId = $result->getId();
                        //M1 > M2 Translation End
                    } catch (Mage_Core_Exception $e) {
                        throw new \Exception($e->getMessage() . ' (' . $e->getCustomMessage() . ')');
                    }

                    // save to mapping table
                    $this->commErpCatalogCategoryFactory->create()
                        ->setErpCode($group)
                        ->setMagentoId($groupId)
                        ->setData('created_at', $this->dateTimeDateTime->gmtDate())
                        ->setData('updated_at', $this->dateTimeDateTime->gmtDate())
                        ->save()
                    ;
                }
            }
        }

        return $groupId;
    }

}
