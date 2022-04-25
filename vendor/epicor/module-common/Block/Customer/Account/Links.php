<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */


namespace Epicor\Common\Block\Customer\Account;


class Links extends \Magento\Framework\View\Element\Html\Links
{
    /* @var $exclude_links for hiding account menu options */

    private $exclude_links = [];

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Epicor\Common\Helper\Access
     */
    protected $commonAccessHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Epicor\Common\Helper\FunctionReader
     */
    protected $helperReader;

    /**
     * @var \Epicor\AccessRight\Model\Authorization
     */
    protected $_accessauthorization;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Epicor\Comm\Helper\Data $commHelper,
        \Epicor\Common\Helper\Access $commonAccessHelper,
        \Epicor\Common\Helper\FunctionReader $helperReader,
        array $data = []
    )
    {
        $this->commHelper = $commHelper;
        $this->commonAccessHelper = $commonAccessHelper;
        $this->scopeConfig = $context->getScopeConfig();
        $this->helperReader = $helperReader;
        $this->_accessauthorization = $context->getAccessAuthorization();
        parent::__construct($context, $data);
    }

    /**
     * @return bool
     */
    protected function _isAccessAllowed($code)
    {
        return $this->_accessauthorization->isAllowed($code);
    }

    public function getLinks()
    {
        $links = parent::getLinks();

        $results = [];
        foreach ($links as $link) {
            if ($link->getResource() && !$this->_isAccessAllowed($link->getResource())) {
                continue;
            }
            if ($msgtype = $link->getData('msgtype')) {
                $msgAvailable = $this->commHelper->checkMsgAvailable(strtoupper($msgtype));
                if (!$msgAvailable) {
                    continue;
                }
            }

            if ($accessFunction = $link->getData('accessFunction')) {
                $function = explode('::', $accessFunction);

                //M1 > M2 Translation Begin (Rule p2-7)
                //$helper = Mage::helper($function[0]);
                $helper = $this->helperReader->getHelper($function[0]);
                //M1 > M2 Translation End

                $function = $function[1];
                if (!$helper->$function()) {
                    continue;
                }
            }

            $helper = $this->commonAccessHelper;
            $allowed = $helper->canAccessUrl($link->getPath(), false, true);
            $this->exclude_links = array_merge(
                explode(',', $this->scopeConfig->getValue('customer/account_menu_options/menu_custom_disallowed', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)),
                $this->exclude_links
            );

            if ($allowed) {
                if (in_array($link->getNameInLayout(), $this->exclude_links)) {
                    continue;
                }
            } else {
                continue;
            }

            $results[] = $link;
        }

        return $results;
    }

    public function removeLinkByName($name)
    {
        $this->exclude_links[] = $name;
    }

    public function _toHtml()
    {
        if (count($this->getLinks()) == 0) {
            $html = '';
        } else {
            $html = parent::_toHtml();
        }

        return $html;
    }
}