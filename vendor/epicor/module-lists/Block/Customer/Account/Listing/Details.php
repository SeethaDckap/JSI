<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Block\Customer\Account\Listing;


use Magento\Store\Model\ScopeInterface;

/**
 * creating  list detail block for add/edit
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Details extends \Magento\Framework\View\Element\Template
{

    /**
     * Config path for Selecting List Type to be included for creation
     */
    const XML_PATH_ALLOWED_LIST_TYPES = "epicor_lists/global/allowed_list_types";

    protected $listId = null;
    protected $list = false;

    /**
     * @var \Epicor\Lists\Model\ListModelFactory
     */
    protected $listsListModelFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $formKey;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;

    protected $listType;
    
     /**
     * @var \Epicor\Lists\Model\ListModel\Type\AbstractModel
     */
    protected $listsListModelTypeAbstractModel;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Epicor\Lists\Model\ListModelFactory $listsListModelFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Epicor\Lists\Model\ListModel\Type $listType,
        \Epicor\Lists\Model\ListModel\Type\AbstractModel $listsListModelTypeAbstractModel,
        array $data = []
    )
    {
        $this->listsListModelFactory = $listsListModelFactory;
        $this->customerSession = $customerSession;
        $this->formKey = $formKey;
        $this->_localeResolver = $localeResolver;
        $this->listType = $listType;
        $this->listsListModelTypeAbstractModel = $listsListModelTypeAbstractModel;
        parent::__construct(
            $context,
            $data
        );
    }


    /**
     * Returns list
     *
     * @return \Epicor\Lists\Model\ListModel
     */
    public function getListId()
    {
        if ($this->getRequest()->getParam('id')) {
            $listId = base64_decode($this->getRequest()->getParam('id'));
            $this->customerSession->unsProductInfo();
            return $listId;
        }
    }

    /**
     * Returns list
     *
     * @return int
     */
    public function getList()
    {
        if (!$this->list) {
            $this->list = $this->listsListModelFactory->create()->load($this->getListId());
        }

        return $this->list;
    }

    //M1 > M2 Translation Begin (Rule p2-1)
    public function getListModel()
    {
        return $this->listsListModelFactory->create();
    }
    //M1 > M2 Translation End

    //M1 > M2 Translation Begin (Rule p2-5.1)
    public function getCustomerSession()
    {
        return $this->customerSession;
    }

    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }
    //M1 > M2 Translation End


    //M1 > M2 Translation Begin (Rule p2-6.4)
    public function getResolver()
    {
        return $this->_localeResolver;
    }
    //M1 > M2 Translation End
    /**
     * Returns url used for products
     *
     * @return string
     */
    public function getListCodeValidateUrl()
    {
        $args = array();
        if ($this->getListId()) {
            $args['list_id'] = $this->getListId();
        }
        return $this->getUrl('epicor_lists/lists/validateCode', $args);
    }

    /**
     * Returns url used for products
     *
     * @return string     
     */
    public function getProductUrl()
    {

        $args = array();
        if ($this->getListId()) {

            $args['list_id'] = $this->getListId();
        }


        return $this->getUrl('epicor_lists/lists/products', $args);
    }

    /**
     * Returns url used for customers
     *
     * @return string
     */
    public function getCustomerUrl()
    {

        $args = array();
        if ($this->getListId()) {

            $args['list_id'] = $this->getListId();
        }


        return $this->getUrl('epicor_lists/lists/customers', $args);
    }

    /**
     * Returns List start date
     *
     * @return string
     */
    public function getStartDate()
    {
        $list = $this->getList();
        $format = \Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT;
        $start_date = '';
        if ($list->getStartDate()) {
            //M1 > M2 Translation Begin (Rule p2-6.4)
            //$date = Mage::app()->getLocale()->date($list->getStartDate(), $format)->toString($format);
            $date = $this->_localeDate->date($list->getStartDate())->format($format);
            //M1 > M2 Translation End

            $dateSplit = explode(' ', $date);
            $start_date = $dateSplit[0];
        }
        return $start_date;
    }

    /**
     * Returns List end date
     *
     * @return string
     */
    public function getEndDate()
    {
        $list = $this->getList();
         $format = \Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT;
        $end_date = '';
        if ($list->getEndDate()) {
            //M1 > M2 Translation Begin (Rule p2-6.4)
            //$date = Mage::app()->getLocale()->date($list->getEndDate(), $format)->toString($format);
            $date = $this->_localeDate->date($list->getEndDate())->format($format);
            //M1 > M2 Translation End
            $dateSplit = explode(' ', $date);
            $end_date = $dateSplit[0];
        }
        return $end_date;
    }

    /**
     * Returns List start time
     *
     * @return array
     */
    public function getStartTime()
    {
        $list = $this->getList();
         $format = \Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT;
        $start_time = '';
        if ($list->getStartDate()) {
            //M1 > M2 Translation Begin (Rule p2-6.4)
            //$date = Mage::app()->getLocale()->date($list->getStartDate(), $format)->toString($format);
            $date = $this->_localeDate->date($list->getStartDate())->format($format);
            //M1 > M2 Translation End
            $dateSplit = explode(' ', $date);
            $start_date = $dateSplit[0];
            $start_time = explode(':', $dateSplit[1]);
        }
        return $start_time;
    }

    /**
     * Returns List End time
     *
     * @return array
     */
    public function getEndTime()
    {
        $list = $this->getList();
         $format = \Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT;
        $end_time = '';
        if ($list->getEndDate()) {
            //M1 > M2 Translation Begin (Rule p2-6.4)
            //$date = Mage::app()->getLocale()->date($list->getEndDate(), $format)->toString($format);
            $date = $this->_localeDate->date($list->getEndDate())->format($format);
            //M1 > M2 Translation End
            $dateSplit = explode(' ', $date);
            $end_date = $dateSplit[0];
            $end_time = explode(':', $dateSplit[1]);
        }
        return $end_time;
    }

    public function getListType()
    {
        $obj = $this->listType;
        
        return $obj;
    }

    /**
     * @var $instance Epicor_Lists_Model_ListModel_Type_AbstractModel
     */
    public function getTypeInstance()
    {
        return $this->listsListModelTypeAbstractModel;

    }

    /**
     * Gets the list types that can be created from frontend
     *
     * @return array|string[]
     */
    public function getAllowedListTypes()
    {
        $configList = $this->_scopeConfig->getValue(
            self::XML_PATH_ALLOWED_LIST_TYPES,
            ScopeInterface::SCOPE_STORE
        );
        $configListTypes = explode(',', $configList);
        $configListTypes = array_flip($configListTypes);
        $listTypes = $this->listType->toListFilterArray();
        $allowedListTypes = array_intersect_key($listTypes, $configListTypes);
        $currentListType = $this->getList()->getType();
        if (!is_null($currentListType)
            && !array_key_exists($currentListType, $allowedListTypes)
            && isset($listTypes[$currentListType])
        ) {
            $allowedListTypes = [
                $currentListType => $listTypes[$currentListType]
            ];
        }
        return $allowedListTypes;
    }


    /**
     * Returns current info
     *
     * @return array
     */
    public function getProductinfo()
    {
        $info = '';
        if ($this->getRequest()->getParam('cartItems')) {
            $info = $this->getRequest()->getParam('cartItems');
            $this->customerSession->setProductInfo($info);
        }
        return $info;
    }
}
