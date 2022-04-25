<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Template\Links;


/**
 * 
 * Wishlist link override block
 * 
 *  - adds access check to wishlist link display
 * 
 * @category   Epicor
 * @package    Epicor_Common
 * @author     Epicor Websales Team
 */
class Wishlist extends \Magento\Wishlist\Block\Link
{

    /**
     * @var \Epicor\Common\Helper\Access
     */
    protected $commonAccessHelper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Wishlist\Helper\Data $wishlistHelper,
        \Epicor\Common\Helper\Access $commonAccessHelper,
        array $data = []
    ) {
        $this->commonAccessHelper = $commonAccessHelper;
        parent::__construct(
            $context,
            $wishlistHelper,
            $data
        );
    }


    protected function _toHtml()
    {
        $helper = $this->commonAccessHelper;
        /* @var $helper \Epicor\Common\Helper\Access */

        $html = parent::_toHtml();
        if (!$helper->canAccessUrl($this->getUrl())) {
            $html = '';
        }
        return $html;
    }

}
