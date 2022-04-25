<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Customer\Returns\Lines;


/**
 * Return Line attachments grid container
 * 
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Attachments extends \Epicor\Common\Block\Generic\Listing
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
        $line = $this->registry->registry('current_return_line');
        /* @var $rfq Epicor_Comm_Model_Customer_ReturnModel_Line */

        $this->_controller = 'customer_returns_lines_attachments';
        $this->_blockGroup = 'Epicor_Comm';
        $this->_headerText = __('Attachments');

        $return = $this->registry->registry('return_model');
        /* @var $return Epicor_Comm_Model_Customer_ReturnModel */
        $allowed = ($return) ? $return->isActionAllowed('Attachments') : true;

        if (!$this->registry->registry('review_display') && $allowed) {
            $this->addButton(
                'submit', array(
                'id' => 'add_return_line_attachments_' . $line->getUniqueId(),
                'label' => __('Add'),
                'class' => 'save return_line_attachment_add attachments_add',
                ), -100
            );
        }
    }

    protected function _postSetup()
    {
        $this->setBoxed(false);
        parent::_postSetup();
    }
    
    protected function _prepareLayout()
    {
        // this is needed for frontend grid use to stop search options being retained for future users. the omission of calling the parent is intentional
        // as all the processing required when calling parent:: should be included
        $this->setChild( 'grid',
            $this->getLayout()->createBlock(
                str_replace(
                    '_',
                    '\\',
                    $this->_blockGroup
                ) . '\\Block\\' . str_replace(
                    ' ',
                    '\\',
                    ucwords(str_replace('_', ' ', $this->_controller))
                ) . '\\Grid',
                $this->_controller . '.grid'.rand()
            )->setSaveParametersInSession(false) );

        $this->toolbar->pushButtons($this, $this->buttonList);
        
        return $this;
    }

}
