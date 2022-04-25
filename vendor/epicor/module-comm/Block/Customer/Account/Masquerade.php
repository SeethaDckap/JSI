<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Block\Customer\Account;


/**
 * Masquerade B2b Account.
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Masquerade extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;


    /**
     * Masquerade constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Epicor\Comm\Helper\Data                         $commHelper
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Epicor\Comm\Helper\Data $commHelper,
        array $data = []
    ) {
        $this->commHelper = $commHelper;
        parent::__construct(
            $context,
            $data
        );
    }

    public function _construct()
    {
        parent::_construct();
        $this->setTitle(__('Account Selector'));
    }

    /**
     * @return string
     */
    public function getReturnUrl()
    {
        $url = $this->_urlBuilder->getCurrentUrl();

        return $this->commHelper->getUrlEncoder()->encode($url);
    }

    /**
     * @return string
     */
    public function getFormUrl()
    {
        return $this->getUrl('epicor_comm/masquerade/masquerade');
    }


}
