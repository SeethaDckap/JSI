<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Block\Claims\Details\Quotes;


/**
 * RFQ contacts grid container
 *
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Contacts extends \Epicor\Customerconnect\Block\Customer\Rfqs\Details\Contacts
{
    const FRONTEND_RESOURCE_CREATE = "Dealer_Connect::dealer_claim_create";
    const FRONTEND_RESOURCE_EDIT = 'Dealer_Connect::dealer_claim_edit';

    protected function _setupGrid()
    {
        $this->_controller = 'customer_rfqs_details_contacts';
        $this->_blockGroup = 'Epicor_Customerconnect';
        $this->_headerText = __('Contacts');

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

        if($showAdd){
            if ($this->registry->registry('rfqs_editable')) {
                $this->buttons->add(
                    'add_contact', array(
                    'id' => 'add_contact',
                    'label' => __('Add'),
                    'class' => 'add',
                ), -100
                );
            }
        }

        $this->removeButton('add_attachment');
    }

    protected function _prepareLayout()
    {
        // this is needed for frontend grid use to stop search options being retained for future users. the omission of calling the parent is intentional
        // as all the processing required when calling parent:: should be included
        if( !$this->getLayout()->getBlock($this->_controller . '.grid')){
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
                $this->_controller . '.grid'
            )->setSaveParametersInSession(false) );

        $this->toolbar->pushButtons($this, $this->buttonList);
        }
        return $this;
    }
}
