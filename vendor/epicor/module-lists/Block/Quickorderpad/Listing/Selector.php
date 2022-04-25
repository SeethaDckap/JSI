<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Lists\Block\Quickorderpad\Listing;


class Selector extends \Epicor\Comm\Block\Customer\Info
{

    protected $_erpAccounts;

    /**
     * @var \Epicor\Lists\Helper\Frontend\Quickorderpad
     */
    protected $listsQopHelper;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Magento\Framework\Url\EncoderInterface
     */
    protected $urlEncoder;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Epicor\Lists\Helper\Frontend\Quickorderpad $listsQopHelper,
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        array $data = []
    )
    {
        $this->listsQopHelper = $listsQopHelper;
        $this->commHelper = $commHelper;
        $this->urlEncoder = $urlEncoder;
        parent::__construct(
            $context,
            $data
        );
    }

    public function _construct()
    {
        parent::_construct();

        $this->setTitle(__('List Selector'));
    }

    public function isSessionList($list)
    {
        $sessionList = $this->listsQopHelper->getSessionList();

        if ($sessionList) {
            return $sessionList->getId() == $list->getId();
        }

        return false;
    }

    public function getLists($scope = null)
    {
        return $this->listsQopHelper->getQuickOrderPadLists('all', $scope);
    }

    public function listsEnabled()
    {
        return $this->listsQopHelper->listsEnabled();
    }

    public function getActionUrl()
    {
        return $this->getUrl('quickorderpad/form/listselect');
    }

    public function getReturnUrl()
    {
        $url = $this->getUrl('quickorderpad/return/results');
        return $this->urlEncoder->encode($url);
    }

}
