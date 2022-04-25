<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Rfqs\Details;


/**
 * RFQ Details page js error show (shows an error in a child iframe to be displayed in parent window)
 * 
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Showerror extends \Magento\Framework\View\Element\Template
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
        $this->setTemplate('customerconnect/customer/account/rfqs/details/show_error.phtml');
    }

    public function getError()
    {
        return $this->registry->registry('rfq_error');
    }

    /**
     * get error message
     * @return array
     */
    public function getErrorMessage()
    {
        //additional message
        $message = $this->registry->registry('message_error');
        if (isset($message['text'])) {
            //remove line breaks
            $message['text'] = preg_replace("/\r|\n/", "", $message['text']);
            return $message;
        } else {
            return array();
        }
    }

}
