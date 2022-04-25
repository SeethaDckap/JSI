<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Block\Account\Dashboard;


/**
 * @method string getTitle()
 * @method void setTitle(string $title)
 * @method void setOnRight(bool $bool)
 * @method bool getOnRight()
 */
class Information extends \Magento\Framework\View\Element\Template
{

    /**
     *  @var \Magento\Framework\DataObject 
     */
    protected $_customer;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $customerSession;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\SessionFactory $customerSession,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        parent::__construct(
            $context,
            $data
        );
    }


    public function _construct()
    {
        parent::_construct();
        $this->setTitle(__('Sales Rep Information'));
        $this->setTemplate('Epicor_SalesRep::epicor/salesrep/account/dashboard/information.phtml');

        $this->_customer = $this->customerSession->create()->getCustomer();
        /* @var $this->_customer Epicor\Comm\Model\Customer */
    }

    public function getName()
    {
        return $this->_customer->getName();
    }

    public function getFunction()
    {
        return $this->_customer->getEccFunction();
    }

    public function getEmail()
    {
        return $this->_customer->getEmail();
    }

    public function getTelephoneNumber()
    {
        return $this->_customer->getEccTelephoneNumber();
    }

    public function getFaxNumber()
    {
        return $this->_customer->getEccFaxNumber();
    }

}
