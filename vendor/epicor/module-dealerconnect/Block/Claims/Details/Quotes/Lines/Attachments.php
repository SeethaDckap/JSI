<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Block\Claims\Details\Quotes\Lines;


/**
 * Dealerconnect Line attachments grid container
 *
 * @category   Epicor
 * @package    Epicor_Dealerconnect
 * @author     Epicor Websales Team
 */
class Attachments extends \Epicor\Customerconnect\Block\Customer\Rfqs\Details\Lines\Attachments
{

    const FRONTEND_RESOURCE_CREATE = "Dealer_Connect::dealer_claim_create";
    const FRONTEND_RESOURCE_EDIT = 'Dealer_Connect::dealer_claim_edit';

    protected function _setupGrid()
    {
        $rfq = $this->registry->registry('current_rfq_row');
        /* @var $rfq \Epicor\Common\Model\Xmlvarien */

        $this->_controller = 'customer_rfqs_details_lines_attachments';
        $this->_blockGroup = 'Epicor_Customerconnect';
        $this->_headerText = __('Attachments');

        $showAdd = false;
        $action = $this->getRequest()->getActionName();

        if($this->_isAccessAllowed(static::FRONTEND_RESOURCE_EDIT) &&
            $this->_isAccessAllowed(static::FRONTEND_RESOURCE_CREATE)){
            $showAdd = true;
        }else if($this->_isAccessAllowed(static::FRONTEND_RESOURCE_EDIT) &&
            !$this->_isAccessAllowed(static::FRONTEND_RESOURCE_CREATE)){
            $showAdd = true;
        }else if(!$this->_isAccessAllowed(static::FRONTEND_RESOURCE_EDIT) &&
            $this->_isAccessAllowed(static::FRONTEND_RESOURCE_CREATE) &&
            $action =='newquote'
        ){
            $showAdd = true;
        }


        if (($this->registry->registry('rfqs_editable') || $this->registry->registry('rfqs_editable_partial'))
            && $showAdd
        ) {
            $this->addButton(
                'submit', array(
                'id' => 'add_line_attachment_' . $rfq->getUniqueId(),
                'label' => __('Add'),
                'class' => 'save rfq_line_attachment_add',
            ), -100
            );
        }
    }


}
