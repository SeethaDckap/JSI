<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Customer\Returns;


/**
 * Customer Orders list
 */
class Lines extends \Epicor\Common\Block\Generic\Listing
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
        $this->registry = $registry;
        parent::__construct(
            $context,
            $data
        );
    }
    protected function _setupGrid()
    {
        $this->_controller = 'customer_returns_lines';
        $this->_blockGroup = 'Epicor_Comm';
        $this->_headerText = __('Lines');
    }

    protected function _postSetup()
    {
        if ($this->registry->registry('details_display')) {
            $this->setBoxed(true);
        }

        parent::_postSetup();
    }
    
    
}
