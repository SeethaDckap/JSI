<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Lists\Block\Customer\Account\Contracts\Parts;

/**
 * Customer Orders list
 */
class Listing extends \Epicor\Common\Block\Generic\Listing {

    /**
     * @var \Epicor\Common\Helper\Access
     */
    protected $commonAccessHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
        \Epicor\Common\Helper\Access $commonAccessHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Block\Widget\Context $context,
    array $data = []
    ) {
        $this->commonAccessHelper = $commonAccessHelper;
        $this->registry = $registry;
        parent::__construct($context, $data);
    }

    protected function _setupGrid() {
        $this->_controller = 'customer_account_contracts_parts_listing';
        $this->_blockGroup = 'Epicor_Lists';
        $this->_headerText = __('Parts');

        $helper = $this->commonAccessHelper;
        /* @var $helper Epicor_Common_Helper_Access */
        $details = $this->registry->registry('epicor_lists_contracts_details');
    }

    protected function _postSetup() {
        $this->setBoxed(true);
        parent::_postSetup();
    }

}
