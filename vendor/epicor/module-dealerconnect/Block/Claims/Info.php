<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Block\Claims;


class Info extends \Magento\Framework\View\Element\Template
{
    /**
     *  @var \Magento\Framework\DataObject
     */
    protected $_infoData = array();
    protected $_extraData = array();
    protected $_linkTo = array();

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $backendHelper;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;

    protected $urlEncoder;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->backendHelper = $backendHelper;
        $this->_localeResolver = $localeResolver;
        $this->urlEncoder = $urlEncoder;
        parent::__construct(
            $context,
            $data
        );
    }


    public function _construct()
    {
        parent::_construct();
    }

    /**
     * @return \Magento\Framework\DataObject
     */
    public function getInfoData()
    {
        return $this->_infoData;
    }

    public function getExtraData()
    {
        return $this->_extraData;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function registry($key)
    {
        return $this->registry->registry($key);
    }

    public function check_your_datetime($myDateString) {
        if(!is_string($myDateString)){
            return false;
        }
        $valid =false;
        if(substr_count($myDateString,'-') > 1) {
            $valid = true;
        }
        return (bool)strtotime($myDateString) && $valid;
    }

    public function decamelize($string) {
        return strtolower(preg_replace(['/([a-z\d])([A-Z])/', '/([^_])([A-Z][a-z])/'], '$1_$2', $string));
    }

    public function renderDate($date)
    {
        $data = __('N/A');
        if (!empty($date)) {
            try {
                $data = $this->getHelper()->getLocalDate($date, \IntlDateFormatter::MEDIUM);
            } catch (\Exception $ex) {
            }
        }

        return $data;
    }
}