<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Block\Customer\Rfqs\Details\Lines;


/**
 * RFQ Line attachments grid container
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
        $this->registry = $registry;
        $this->buttons = $context->getButtonList();
        parent::__construct(
            $context,
            $data
        );
    }

    protected function _setupGrid()
    {
        $rfq = $this->registry->registry('current_rfq_row');
        /* @var $rfq \Epicor\Common\Model\Xmlvarien */

        $this->_controller = 'customer_rfqs_details_lines_attachments';
        $this->_blockGroup = 'Epicor_Customerconnect';
        $this->_headerText = __('Attachments');

        if (($this->registry->registry('rfqs_editable') || $this->registry->registry('rfqs_editable_partial'))
            && $this->_isFormAccessAllowed()
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
                $this->_controller . '.grid.'.$this->mathRandom->getRandomString(10)
            )->setSaveParametersInSession(false) );

        $this->toolbar->pushButtons($this, $this->buttons);
        return $this;
    }
    
        public function getButtonsHtml($region = null)
    {
        $out = '';

        foreach ($this->buttons->getItems() as $buttons) {
            unset($buttons['add_attachment']);
            unset($buttons['add_search']);
            unset($buttons['add_line']);
            unset($buttons['add_contact']);
            unset($buttons['newline_button']);
            unset($buttons['clone_selected']);
            unset($buttons['delete_selected']);
            unset($buttons['bom']);
            
            
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
