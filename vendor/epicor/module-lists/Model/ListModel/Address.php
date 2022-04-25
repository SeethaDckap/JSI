<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Model\ListModel;

use Epicor\Lists\Model\ListModel\Address\Restriction;
use Epicor\Lists\Model\ListModel\Address\RestrictionFactory;
use Epicor\Lists\Model\ResourceModel\ListModel\Address\CollectionFactory;

/**
 * Model Class for List
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 *
 * @method string getListId()
 * @method string getAddressCode()
 * @method string getPurchaseOrderNumber()
 * @method string getName()
 * @method string getAddress1()
 * @method string getAddress2()
 * @method string getAddress3()
 * @method string getCity()
 * @method string getCounty()
 * @method string getCountry()
 * @method string getPostcode()
 * @method string getTelephoneNumber()
 * @method string getMobileNumber()
 * @method string getFaxNumber()
 * @method string getEmailAddress()
 * @method string setListId()
 * @method string setAddressCode()
 * @method string setPurchaseOrderNumber()
 * @method string setName()
 * @method string setAddress1()
 * @method string setAddress2()
 * @method string setAddress3()
 * @method string setCity()
 * @method string setCounty()
 * @method string setCountry()
 * @method string setPostcode()
 * @method string setTelephoneNumber()
 * @method string setMobileNumber()
 * @method string setFaxNumber()
 * @method string setEmailAddress()
 * @method setRestrictionListId(string $string)
 * @method string getRestrictionListId()
 * @method setRestrictionType(string $string)
 * @method string getRestrictionType()
 */
class Address extends \Epicor\Database\Model\Lists\Address
{
    /**
     * @var RestrictionFactory
     */
    protected $listsListModelAddressRestrictionFactory;

    /**
     * @var \Epicor\Lists\Helper\Data
     */
    protected $listsHelper;

    /**
     * @var CollectionFactory
     */
    protected $listsResourceListModelAddressCollectionFactory;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        RestrictionFactory $listsListModelAddressRestrictionFactory,
        \Epicor\Lists\Helper\Data $listsHelper,
        CollectionFactory $listsResourceListModelAddressCollectionFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->listsListModelAddressRestrictionFactory = $listsListModelAddressRestrictionFactory;
        $this->listsHelper = $listsHelper;
        $this->listsResourceListModelAddressCollectionFactory = $listsResourceListModelAddressCollectionFactory;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }


    public function _construct()
    {
        $this->_init('Epicor\Lists\Model\ResourceModel\ListModel\Address');
    }

    public function getFlattenedAddress()
    {
        $fields = array(
            'address1',
            'address2',
            'address3',
            'city',
            'county',
            'country',
            'postcode',
            'telephone_number',
            'mobile_number',
            'fax_number'
        );

        $dataArray = array();
        foreach ($fields as $field) {
            $data = $this->getData($field);
            if ($data) {
                $dataArray[] = $data;
            }
        }

        return join(', ', $dataArray);
    }


    public function afterSave()
    {
        parent::afterSave();

        if ($this->isObjectNew() && $this->getRestrictionType()) {
            $restrictedPurchase = $this->listsListModelAddressRestrictionFactory->create();
            /* @var $restrictedPurchase \Epicor\Lists\Model\ListModel\Address\Restriction */
            $restrictedPurchase->setListId($this->getListId());
            $restrictedPurchase->setAddressId($this->getId());
            $restrictedPurchase->setRestrictionType($this->getRestrictionType());
            $restrictedPurchase->save();
        }
    }

    public function validateRestriction()
    {
        if ($this->getRestrictionType()) {
            $restrictionType = $this->getRestrictionType();
            /* @var $helper \Epicor\Lists\Helper\Data */
            $helper = $this->listsHelper;

            $addressCollection = $this->listsResourceListModelAddressCollectionFactory->create();
            /* @var $addressCollection \Epicor\Lists\Model\ResourceModel\ListModel\Address\Collection */
            $restrictionTable = $addressCollection->getTable('ecc_list_address_restriction');
            $addressCollection->getSelect()
                ->join(array('r' => $restrictionTable), 'main_table.id=r.address_id', array());
            $addressCollection
                ->addFieldToFilter('r.list_id', $this->getListId())
                ->addFieldToFilter('restriction_type', $restrictionType);

            switch ($this->getRestrictionType()) {
                case Restriction::TYPE_ZIP:
                    $postcode = $helper->formatPostcode($this->getPostcode());
                    $addressCollection->addFieldToFilter('country', $this->getCountry())
                        ->addFieldToFilter('postcode', $postcode);
                    break;
                case Restriction::TYPE_COUNTRY:
                    $addressCollection->addFieldToFilter('country', $this->getCountry());
                    break;
                case Restriction::TYPE_STATE:
                    $addressCollection->addFieldToFilter('country', $this->getCountry())
                        ->addFieldToFilter('county', $this->getCounty());
                    break;
                case Restriction::TYPE_ADDRESS:
                    $postcode = $helper->formatPostcode($this->getPostCode());
                    $addressCollection->addFieldToFilter('country', $this->getCountry())
                        ->addFieldToFilter('county', $this->getCounty())
                        ->addFieldToFilter('postcode', $postcode)
                        ->addFieldToFilter('address1', $this->getAddress1())
                        ->addFieldToFilter('address2', $this->getAddress2())
                        ->addFieldToFilter('address3', $this->getAddress3())
                        ->addFieldToFilter('name', $this->getName());
                    break;
            }

            if ($addressCollection->count() >= 1) {
                return false;
            }
        }

        return true;
    }
}
