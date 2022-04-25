<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Block\Claims\Details\Quotes;


/**
 * RFQ details attachments grid container
 *
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Attachments extends \Epicor\Customerconnect\Block\Customer\Rfqs\Details\Attachments
{
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
