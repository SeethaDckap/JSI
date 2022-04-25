<?php
/**
 * Copyright © 2010-2019 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Block\Customer\Account;

use Magento\Customer\Block\Account\SortLinkInterface;

/**
 * Change Company link
 */
class CompanyLink extends \Magento\Framework\View\Element\Html\Link implements SortLinkInterface
{
    const FRONTEND_RESOURCE = "Epicor_Customerconnect::change_company_link";

    protected $_template ="Epicor_Comm::epicor_comm/customer/account/companylink.phtml";

    /**
     * @var \Epicor\AccessRight\Model\Authorization
     */
    protected $_authorization;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\App\DefaultPathInterface $defaultPath
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    ) {
        $this->_authorization = $context->getAccessAuthorization();
        $this->customerSession = $customerSession;
        parent::__construct($context, $data);
    }
    
    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        $customer = $this->customerSession->getCustomer();
        $erpAccounts = $customer->getErpAcctCounts();
        if(!$this->_isAccessAllowed(static::FRONTEND_RESOURCE)
            || empty($erpAccounts) || count($erpAccounts) == 1){
            return '';
        }
               
        return parent::_toHtml();
    }

    /**
     * {@inheritdoc}
     * @since 101.0.0
     */
    public function getSortOrder()
    {
        return $this->getData(self::SORT_ORDER);
    }

    /**
     * @param $code
     * @return bool
     */
    public function _isAccessAllowed($code)
    {
        return $this->_authorization->isAllowed($code);
    }
}
