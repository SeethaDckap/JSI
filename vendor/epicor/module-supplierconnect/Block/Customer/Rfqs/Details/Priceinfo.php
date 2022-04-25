<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Customer\Rfqs\Details;


class Priceinfo extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_localeResolver  = $localeResolver;
        $this->registry = $registry;
        parent::__construct(
            $context,
            $data
        );
    }


    public function _construct()
    {
        parent::_construct();

        $this->setTemplate('supplierconnect/customer/rfq/priceinfo.phtml');
        $this->setTitle(__('Price Information'));
    }

    /**
     * Gets Price Per options from the RFQ provided
     * 
     * @param \Epicor\Common\Model\Xmlvarien $rfq
     * 
     * @return array
     */
    public function getPricePerOptions($rfq)
    {
        $labels = array(
            'E' => '/1',
            'C' => '/100',
            'M' => '/1000',
        );

        $options = array();

        if ($rfq->getPricePerOptions()) {
            foreach ($rfq->getPricePerOptions()->getasarrayOption() as $option) {
                if (isset($labels[$option])) {
                    $options[$option] = $labels[$option];
                }
            }
        }

        if (empty($options)) {
            $options = $labels;
        }

        return $options;
    }

    /**
     * Gets Price Break Modifier from the RFQ provided
     * 
     * @param \Epicor\Common\Model\Xmlvarien $rfq
     * 
     * @return array
     */
    public function getPriceBreakModifierOptions($rfq)
    {
        $labels = array(
            '$' => 'Flat Unit Price',
            '%' => 'Percentage of Base',
        );

        $options = array();

        if ($rfq->getPriceBreakModifierOptions()) {
            foreach ($rfq->getPriceBreakModifierOptions()->getasarrayOption() as $option) {
                if (isset($labels[$option])) {
                    $options[$option] = $labels[$option];
                }
            }
        }

        if (empty($options)) {
            $options = $labels;
        }

        return $options;
    }

    /**
     * Gets Days difference between Effective and expires date
     * 
     * @param \Epicor\Common\Model\Xmlvarien $rfq
     * 
     * @return integer
     */
    public function getDays($rfq)
    {
        $days = '';
        if ($rfq->getExpiresDate()) {

            $datetime1 = new \DateTime(date('Y-m-d 00:00:00', strtotime($rfq->getEffectiveDate())));
            $datetime2 = new \DateTime(date('Y-m-d 23:59:59', strtotime($rfq->getExpiresDate())));
            $days = $datetime1->diff($datetime2)->days;
        }

        return $days;
    }

    public function getResolver()
    {
        return $this->_localeDate;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function registry($key)
    {
        return $this->registry->registry($key);
    }

    /**
     * @param $key
     * @param $value
     * @param bool $graceful
     */
    public function register($key, $value, $graceful = false)
    {
        $this->registry->register($key, $value, $graceful);
    }

    /**
     * @param $key
     */
    public function unregister($key)
    {
        $this->registry->unregister($key);
    }
}
