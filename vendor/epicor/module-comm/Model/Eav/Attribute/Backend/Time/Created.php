<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Eav\Attribute\Backend\Time;


/**
 * 
 *
 * @category   Mage
 * @package    Mage_Eav
 * @author     Sean Flynn
 */
//include_once("Mage/Eav/Model/Entity/Attribute/Backend/Time/Created.php");

class Created extends \Magento\Eav\Model\Entity\Attribute\Backend\Time\Created
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTimeFactory
     */
    protected $dateTimeDateTimeFactory;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $localDate;

    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateTimeDateTimeFactory
    ) {
        $this->registry = $registry;
        $this->dateTimeDateTimeFactory = $dateTimeDateTimeFactory;
        $this->_localeResolver = $localeResolver;
        $this->localDate = $localeDate;
        parent::__construct(
            $dateTime
        );
    }


    public function beforeSave($object)
    {
        $attributeCode = $this->getAttribute()->getAttributeCode();
        $date = $object->getData($attributeCode);
        if (is_null($date)) {
            if ($object->isObjectNew()) {
                //needs to be system date after offset applied
                if ($this->registry->registry('checkout_save_order')) {
                    $object->setData($attributeCode, date(DATE_ATOM, strtotime("now")));
                } else {
                    $object->setData($attributeCode, $this->dateTimeDateTimeFactory->create()->date('Y-m-d H:i:s')); // creates offset system date for all but account created at checkout
                }
            }
        } else {
            // convert to UTC
            //M1 > M2 Translation Begin (Rule p2-6.4)
            //$zendDate = Mage::app()->getLocale()->utcDate(null, $date, true, $this->_getFormat($date));
            $zendDate = $this->localDate->convertConfigTimeToUtc($date);
            //$zendDate = $this->_localeResolver->getLocale()->utcDate(null, $date, true, $this->_getFormat($date));
            //M1 > M2 Translation End
            $object->setData($attributeCode, $zendDate);
        }

        return $this;
    }

}
