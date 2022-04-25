<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Customer\Returns;


/**
 * Returns creation page, Login block
 * 
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Login extends \Epicor\Comm\Block\Customer\Returns\AbstractBlock
{

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Epicor\Comm\Helper\Returns $commReturnsHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Registry $registry,
        array $data = [])
    {
        $this->customerSession = $customerSession;
        parent::__construct(
            $context,
            $commReturnsHelper,
            $registry,
            $data);
    }
    public function _construct()
    {
        parent::_construct();
        $this->setTitle(__('Login'));
        $this->setTemplate('epicor_comm/customer/returns/login.phtml');
        //
    }

    public function getMessages()
    {
        return $this->customerSession->getMessages(true);
    }

    public function getPostAction()
    {
        return $this->getUrl('customer/account/loginPost', array('_secure' => true));
    }

}
