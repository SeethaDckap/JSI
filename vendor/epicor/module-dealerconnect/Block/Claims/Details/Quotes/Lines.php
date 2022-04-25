<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Block\Claims\Details\Quotes;


/**
 * Dealer Claim RFQ lines grid container
 *
 * @category   Epicor
 * @package    Epicor_Dealerconnect
 * @author     Epicor Websales Team
 */
class Lines extends \Epicor\Customerconnect\Block\Customer\Rfqs\Details\Lines
{
    const FRONTEND_RESOURCE_CREATE = "Dealer_Connect::dealer_claim_create";
    const FRONTEND_RESOURCE_EDIT = 'Dealer_Connect::dealer_claim_edit';

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




    protected function _setupGrid()
    {
        //parent::_setupGrid();
        $this->_controller = 'claims_details_quotes_lines';
        $this->_blockGroup = 'Epicor_Dealerconnect';

        $showAdd = false;
        //$showAdd = true;
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

        if ($showAdd && $this->registry->registry('rfqs_editable')) {
            $this->buttons->add(
                'add_line', array(
                'id' => 'add_line',
                'label' => __('Quick Add'),
                'class' => 'add',
            ), -1
            );

            $this->buttons->add(
                'add_search', array(
                'id' => 'add_search',
                'label' => __('Add by Search'),
                'class' => 'show-hide',
            ), -1
            );

            $this->buttons->add(
                'clone_selected', array(
                'id' => 'clone_selected',
                'label' => __('Clone Selected'),
                'class' => 'go',
            ), 1
            );

            $this->buttons->add(
                'delete_selected', array(
                'id' => 'delete_selected',
                'label' => __('Delete Selected'),
                'class' => 'delete',
            ), 1
            );

            $this->buttons->add(
                'bom', array(
                'id' => 'bom',
                'label' => __('Bill of Materials'),
                'class' => 'bom',
            ), 1
            );
        }
    }
}
