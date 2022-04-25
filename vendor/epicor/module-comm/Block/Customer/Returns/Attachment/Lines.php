<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Customer\Returns\Attachment;


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
        $this->_controller = 'customer_returns_attachment_lines';
        $this->_blockGroup = 'Epicor_Comm';
        $this->_headerText = __('Attachments');

        if (!$this->registry->registry('review_display')) {
            $this->addButton(
                'submit', array(
                'id' => 'add_customer_returns_attachment_lines',
                'label' => __('Add'),
                'class' => 'save return_attachments_add',
                ), -100
            );
        }
    }

    protected function _postSetup()
    {
        if ($this->registry->registry('details_display')) {
            $this->setBoxed(true);
        }
        parent::_postSetup();
    }

    
}
