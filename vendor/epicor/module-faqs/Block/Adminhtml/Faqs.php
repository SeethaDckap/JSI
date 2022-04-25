<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Faqs\Block\Adminhtml;


/**
 * Faqs List admin grid container
 * @category   Epicor
 * @package    Faq
 * @author     Epicor Websales Team
 *
 */
class Faqs extends \Magento\Backend\Block\Widget\Grid\Container
{

    public function _construct()
    {
        $this->_blockGroup = 'Epicor_Faqs';
        $this->_controller = 'adminhtml_faqs';
        $this->_headerText = __('Manage F.A.Q.');
        parent::_construct();
        //Checking user  permission to save and adding/removing a save button
        if ($this->_authorization->isAllowed('Epicor_Faqs::faqs_management')) {
            $this->buttonList->update('add', 'label', __('Add New F.A.Q.'));
        } else {
            $this->buttonList->remove('add');
        }
    }

}
