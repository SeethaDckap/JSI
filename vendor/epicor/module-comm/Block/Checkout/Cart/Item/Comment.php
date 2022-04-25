<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Checkout\Cart\Item;


/**
 * Cart item comment
 * 
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Comment extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Url\Helper\Data
     */
    protected $frameworkHelperDataHelper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
        array $data = []
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        $this->frameworkHelperDataHelper = $frameworkHelperDataHelper;
        parent::__construct(
            $context,
            $data
        );
    }


    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('epicor_comm/checkout/cart/item/comment.phtml');
    }

    public function getItem()
    {
        return $this->getParentBlock()->getItem();
    }

    public function isCommentAllowed()
    {
        $allowed = $this->scopeConfig->isSetFlag('checkout/options/line_comments_enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if ($allowed) {
            $urlString = $this->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true]);
            $types = explode(',', $this->scopeConfig->getValue('checkout/options/show_line_comments', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
            if (strpos($urlString, 'cart')) {
                $type = 'cart';
            } else {
                $type = 'review';
            }

            $allowed = in_array($type, $types);
        }

        if ($allowed) {
            $productAllowed = $this->getItem()->getProduct()->getEccLineCommentsEnabled();

            if (!$productAllowed) {
                $allowed = false;
            }
        }

        return $allowed;
    }

    public function getCommentLimit()
    {
        $limit = 0;

        $limited = $this->scopeConfig->isSetFlag('checkout/options/line_comments_limited', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($limited) {
            $limit = $this->scopeConfig->getValue('checkout/options/max_line_comment_length', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }
        return $limit;
    }

}
