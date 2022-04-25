<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Customer\Returns;


/**
 * Returns creation page, Attachments block
 * 
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Attachments extends \Epicor\Comm\Block\Customer\Returns\AbstractBlock
{

    public function _construct()
    {
        parent::_construct();
        $this->setTitle(__('Attachments'));
        $this->setTemplate('epicor_comm/customer/returns/attachments.phtml');
    }

    public function getAttachmentsHtml()
    {
        return $this->layout->createBlock('\Epicor\Comm\Block\Customer\Returns\Attachment\Lines')->toHtml();
    }

}
