<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Block\Customer\Rfqs\Details;


/**
 * RFQ lines grid container
 *
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Lines extends \Epicor\Common\Block\Generic\Listing
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
        $this->_controller = 'customer_rfqs_details_lines';
        $this->_blockGroup = 'Epicor_Customerconnect';
        $this->_headerText = ''; //Mage::helper('customerconnect')->__('Lines');

        if ($this->registry->registry('rfqs_editable') && $this->_isFormAccessAllowed()) {
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

//            $this->buttons->add(
//                'newline_button', array(
//                'id' => 'newline_button',
//                'label' => '',
//                'class' => '',
//            ), 0
//            );


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
        }
    }

    protected function _postSetup()
    {
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
        foreach ($this->buttons->getItems() as $buttons) {

            if (array_key_exists('add_contact', $buttons)) {
                continue;
            }
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
