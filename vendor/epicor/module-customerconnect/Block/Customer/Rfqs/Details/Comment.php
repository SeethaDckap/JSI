<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Rfqs\Details;


/**
 * RFQ comment block
 * 
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Comment extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->registry = $registry;
        parent::__construct(
            $context,
            $data
        );
    }


    public function _construct()
    {
        parent::_construct();

        $this->setTemplate('Epicor_Customerconnect::customerconnect/customer/account/rfqs/details/comment.phtml');
    }

    public function getComment()
    {
        $rfq = $this->registry->registry('customer_connect_rfq_details');
        return $rfq ? $this->escapeHtml($rfq->getNoteText()) : '';
    }

    //M1 > M2 Translation Begin (Rule p2-8)
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
    //M1 > M2 Translation End
}
