<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Block\Customer\Rfqs\Details;


/**
 * RFQ details attachments grid container
 *
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Attachments extends \Epicor\Common\Block\Generic\Listing
{

    const FRONTEND_RESOURCE_CREATE = 'Epicor_Customerconnect::customerconnect_account_rfqs_create';

    const FRONTEND_RESOURCE_EDIT = 'Epicor_Customerconnect::customerconnect_account_rfqs_edit';
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Backend\Block\Widget\Button\ButtonList
     */
    protected $buttons;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    )
    {
        $this->scopeConfig = $context->getScopeConfig();
        $this->registry = $registry;
        $this->buttons = $context->getButtonList();
        parent::__construct(
            $context,
            $data
        );
    }

    protected function _setupGrid()
    {
        $this->_controller = 'customer_rfqs_details_attachments';
        $this->_blockGroup = 'Epicor_Customerconnect';
        if ($this->scopeConfig->getValue('customerconnect_enabled_messages/CRQD_request/attachment_support', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $this->_headerText = __('Attachments');
            if (($this->registry->registry('rfqs_editable') || $this->registry->registry('rfqs_editable_partial'))
                && $this->_isFormAccessAllowed()) {
                $this->buttons->add(
                    'add_attachment', array(
                    'id' => 'add_attachment',
                    'label' => __('Add'),
                    'class' => 'add',
                ), -100
                );
            }
        } else {
            $this->_headerText = '';
        }
    }

    protected function _postSetup()
    {
        $this->setBoxed(true);
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
                $this->_controller . '.grid'
            )->setSaveParametersInSession(false) );

        $this->toolbar->pushButtons($this, $this->buttons);
        return $this;
    }

    public function getButtonsHtml($region = null)
    {
        $out = '';
        $this->buttonList->remove('add_search');
        $this->buttonList->remove('add_line');
        $this->buttonList->remove('add_contact');
        $this->buttonList->remove('newline_button');
        $this->buttonList->remove('clone_selected');
        $this->buttonList->remove('delete_selected');
        $this->buttonList->remove('bom');

        foreach ($this->buttons->getItems() as $buttons) {
            /** @var \Magento\Backend\Block\Widget\Button\Item $item */
            foreach ($buttons as $item) {
                if ($region && $region != $item->getRegion()) {
                    continue;
                }
                $out .= $this->getChildHtml($item->getButtonKey());
            }
        }
        return $out;
    }

}
