<?php
/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Supplierconnect\Block\Customer\Orders\Details\Lines;


/**
 * Order Line attachments grid container
 *
 * @category   Epicor
 * @package    Epicor_Supplierconnect
 * @author     Epicor Websales Team
 */
class Attachments extends \Epicor\Common\Block\Generic\Listing
{

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;
    
    /**
     * @var \Magento\Backend\Block\Widget\Button\ButtonList
     */
    private $buttons;

    /**
     * Array of buttons to be skipped.
     *
     * @var array
     */
    private $skipButtons = [
        'add_attachment',
    ];

    /**
     * Attachments constructor.
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
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

    /**
     * Grid setup
     */
    protected function _setupGrid()
    {
        $order = $this->registry->registry('current_order_row');
        /* @var $order \Epicor\Common\Model\Xmlvarien */

        $this->_controller = 'customer_orders_details_lines_attachments';
        $this->_blockGroup = 'Epicor_Supplierconnect';
        $this->_headerText = __('Attachments');
        $this->addButton(
            'submit', [
            'id' => 'add_line_attachment_' . $order->getUniqueId(),
            'label' => __('Add'),
            'class' => 'save order_line_attachment_add',
        ], -100
        );
    }

    /**
     * Set Box true for attachments table
     */
    protected function _postSetup()
    {
        $this->setBoxed(true);
        parent::_postSetup();
    }

    /**
     * @return $this|Attachments|\Magento\Backend\Block\Widget\Container|\Magento\Backend\Block\Widget\Grid\Container
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
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

    /**
     * Gets button html
     *
     * @param mixed $region Region.
     *
     * @return string
     */
    public function getButtonsHtml($region=null)
    {
        $out = '';
        foreach ($this->buttons->getItems() as $buttons) {
            foreach ($buttons as $item) {
                $id = $item->getId();
                if (in_array($id, $this->skipButtons) || ($region && $region != $item->getRegion())) {
                    continue;
                }
                $out .= $this->getChildHtml($item->getButtonKey());
            }
        }
        return $out;
    }
}
