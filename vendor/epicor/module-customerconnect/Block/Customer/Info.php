<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Block\Customer;


/**
 * @method string getTitle()
 * @method void setTitle(string $title)
 * @method int getColumnCount()
 * @method void setColumnCount(int $count)
 * @method bool getOnRight()
 * @method void setOnRight(bool $bool)
 */
class Info extends \Epicor\Common\Block\Customer\Info
{
    const FRONTEND_RESOURCE_INFORMATION_READ = 'Epicor_Customerconnect::customerconnect_account_information_information_read';

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $_infoData = array();
    protected $_extraData = array();

    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $customerconnectHelper;

    /**
     * @var \Epicor\AccessRight\Model\Authorization
     */
    protected $_accessauthorization;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        array $data = []
    )
    {
        $this->customerconnectHelper = $customerconnectHelper;
        $this->_accessauthorization = $context->getAccessAuthorization();
        parent::__construct(
            $context,
            $customerconnectHelper,
            $data
        );
    }


    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('Epicor_Customerconnect::customerconnect/info.phtml');
        $this->setColumnCount(3);
    }

    /**
     *
     * @return \Epicor\Customerconnect\Helper\Data
     */
    public function getHelper($type = null)
    {
        //M1 > M2 Translation Begin (Rule p2-7)
        //return Mage::helper('customerconnect');
        return $this->customerconnectHelper;
        //M1 > M2 Translation End
    }


    /**
     * Returns whether to show a field on the page or not
     *
     * @param string $field - field name to check
     *
     * @return boolean
     */
    public function showField($field)
    {
        // customer check
        // erp account check
        // global check
        if ($this->_scopeConfig->isSetFlag('customerconnect_enabled_messages/customer_account_summary/show_' . $field, \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            return true;
        }

        return false;
    }

    public function toHtml()
    {
        if (!$this->_accessauthorization->isAllowed(static::FRONTEND_RESOURCE_INFORMATION_READ)) {
            return '';
        }
        return parent::toHtml();
    }
}
