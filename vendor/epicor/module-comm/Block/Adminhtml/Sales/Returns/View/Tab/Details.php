<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Block\Adminhtml\Sales\Returns\View\Tab;

class Details extends \Magento\Backend\Block\Template implements \Magento\Backend\Block\Widget\Tab\TabInterface {

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout;

    /**
     * @var \Epicor\Comm\Helper\Returns
     */
    protected $commReturnsHelper;

    public function __construct(
    \Magento\Backend\Block\Template\Context $context, \Magento\Framework\Registry $registry, \Epicor\Comm\Helper\Returns $commReturnsHelper, array $data = []
    ) {
        $this->registry = $registry;
        $this->layout = $context->getLayout();
        $this->commReturnsHelper = $commReturnsHelper;
        parent::__construct(
                $context, $data
        );

        parent::_construct();
        $this->_title = 'Details';
        $this->setTemplate('epicor_comm/sales/returns/view/details.phtml');
        $this->registry->register('return_model', $this->getReturn());
        $this->registry->register('review_display', true);
    }

    /**
     * 
     * @return \Epicor\Comm\Model\Customer\ReturnModel
     */
    public function getReturn() {
        return $this->registry->registry('return');
    }

    public function canShowTab() {
        return true;
    }

    public function getTabLabel() {
        return $this->_title;
    }

    public function getTabTitle() {
        return $this->_title;
    }

    public function isHidden() {
        return false;
    }

    public function getLinesHtml() {
        return $this->layout->createBlock('Epicor\Comm\Block\Adminhtml\Sales\Returns\View\Tab\Details\Lines')->toHtml();
    }

    public function getAttachmentsHtml() {
        $helper = $this->commReturnsHelper;
        /* @var $helper Epicor_Comm_Helper_Returns */

        $return = $this->getReturn();

        $html = '';
        if ($helper->checkConfigFlag('return_attachments', $return->getReturnType(), $return->getStoreId())) {
            $html = $this->layout->createBlock('Epicor\Comm\Block\Adminhtml\Sales\Returns\View\Tab\Details\Attachments')->toHtml();
        }

        return $html;
    }

}
