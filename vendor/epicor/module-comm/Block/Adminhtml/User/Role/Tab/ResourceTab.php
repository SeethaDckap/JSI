<?php
/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Comm
 * @subpackage Block
 */
namespace Epicor\Comm\Block\Adminhtml\User\Role\Tab;
use Magento\User\Block\Role\Tab\Edit;
/**
 * Class ResourceTab
 *
 * @package Epicor\Comm\Block\Adminhtml\User\Role\Tab
 */
class ResourceTab extends Edit
{


    /**
     * After html.
     *
     * @param string $html Html.
     *
     * @return string
     */
    public function _afterToHtml($html)
    {
        $html  = parent::_afterToHtml($html);
        $html .= '<b>Note : </b><p>'.__(
            'To provide access for Epicor Returns menu, kindly check <b>Epicor Menu > Manage > Returns</b> and <b>Sales > Epicor Returns.</b>'
        ).'</p>';
        $html .= '<p >'.__(
            'To provide access for Epicor Quotes menu, kindly check <b>Epicor Menu > Manage > Quotes </b> and <b>Sales > Epicor Quotes.</b>'
        ).'</p>';

        return $html;

    }//end _afterToHtml()


}//end class
