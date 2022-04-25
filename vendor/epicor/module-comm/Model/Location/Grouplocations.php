<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Location;


/**
 * Groups Location model
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 * 
 * @method \Epicor\Comm\Model\Location\Grouplocations setGroupId(integer $value)
 * @method \Epicor\Comm\Model\Location\Grouplocations setGroupLocationId(integer $value)
 * @method \Epicor\Comm\Model\Location\Grouplocations setPosition(integer $value)
 * 
 * @method integer GetGroupId()
 * @method integer getGroupLocationId()
 * @method integer getPosition()
 */
class Grouplocations extends \Epicor\Common\Model\AbstractModel
{

//    protected $_eventPrefix = 'ecc_location_product';
//    protected $_eventObject = 'location_product';
//    private $_currencies;
//    private $_deleteCurrencies = array();
//
//    /**
//     * @var \Epicor\Comm\Model\ResourceModel\Location\Product\Currency\CollectionFactory
//     */
//    protected $commResourceLocationProductCurrencyCollectionFactory;
//
//    /**
//     * @var \Epicor\Comm\Model\Location\Product\CurrencyFactory
//     */
//    protected $commLocationProductCurrencyFactory;
//
//    /**
//     * @var \Epicor\Comm\Helper\Messaging
//     */
//    protected $commMessagingHelper;
//
//    /**
//     * @var \Magento\Framework\App\Config\ScopeConfigInterface
//     */
//    protected $scopeConfig;
//
//    public function __construct(
//        \Magento\Framework\Model\Context $context,
//        \Magento\Framework\Registry $registry,
//        \Epicor\Comm\Model\Location\Product\CurrencyFactory $commLocationProductCurrencyFactory,
//        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
//        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
//        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
//        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
//        array $data = [])
//    {
//        $this->commResourceLocationProductCurrencyCollectionFactory = $commResourceLocationProductCurrencyCollectionFactory;
//        $this->commLocationProductCurrencyFactory = $commLocationProductCurrencyFactory;
//        $this->commMessagingHelper = $commMessagingHelper;
//        $this->scopeConfig = $scopeConfig;
//
//        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
//    }


    protected function _construct()
    {
        // initialize resource model
        $this->_init('Epicor\Comm\Model\ResourceModel\Location\Grouplocations');
    }
}
