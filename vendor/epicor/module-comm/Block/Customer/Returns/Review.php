<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Customer\Returns;


/**
 * Returns creation page, Review block
 * 
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Review extends \Epicor\Comm\Block\Customer\Returns\AbstractBlock
{


    /**
     * @var \Epicor\Comm\Helper\Returns
     */
    protected $commReturnsHelper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
         \Epicor\Comm\Helper\Returns $commReturnsHelper,
        \Magento\Framework\Registry $registry,
        array $data = [])
    {
        $this->commReturnsHelper = $commReturnsHelper;
        parent::__construct(
            $context,
            $commReturnsHelper,
            $registry,
            $data);
    }

    public function _construct()
    {
        parent::_construct();
        $this->setTitle(__('Review'));
        $this->setTemplate('epicor_comm/customer/returns/review.phtml');
        $this->registry->register('review_display', 1);
    }

    public function getLinesHtml()
    {
        return $this->layout->createBlock('\Epicor\Comm\Block\Customer\Returns\Lines')->toHtml();
    }

    public function getAttachmentsHtml()
    {
        $helper = $this->commReturnsHelper;
        /* @var $helper Epicor_Comm_Helper_Returns */

        $html = '';
        if ($helper->checkConfigFlag('return_attachments')) {
            $html = $this->layout->createBlock('\Epicor\Comm\Block\Customer\Returns\Attachment\Lines')->toHtml();
        }

        return $html;
    }

    /**
     * 
     * @return \Epicor\Comm\Model\Customer\ReturnModel
     */
    public function getSuccess()
    {
        return $this->registry->registry('return_success');
    }

}
