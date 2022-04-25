<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Block\Adminhtml\Budgets\ErpAccounts\Edit\Tab\Renderer;

use Epicor\OrderApproval\Model\GroupSave\Customers as GroupCustomers;

class Action extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Action
{
    /**
     * @var string
     */
    private $html;

    /**
     * @var \Magento\Framework\DataObject
     */
    private $row;

    /**
     * @var bool
     */
    private $startLink;

    /**
     * @var GroupCustomers
     */
    private $groupCustomers;

    /**
     * Action constructor.
     * @param GroupCustomers $groupCustomers
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param array $data
     */
    public function __construct(
        GroupCustomers $groupCustomers,
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $jsonEncoder,
            $data
        );
        $this->groupCustomers = $groupCustomers;
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        if (!$this->groupCustomers->isEditableByCustomer()) {
            return '';
        }
        $this->row = $row;
        $actions = $this->getColumn()->getActions();
        if (empty($actions) || !is_array($actions)) {
            return '&nbsp;';
        }

        return  $this->renderLinks($actions);
    }

    /**
     * @param array $actions
     * @return string
     */
    private function renderLinks($actions)
    {
        if ($this->getColumn()->getLinks()) {
            $this->html = '';
            $this->startLink = true;
            foreach ($actions as $action) {
                if (is_array($action)) {
                    $this->updateLink($action);
                }
            }
            return $this->html;
        } else {
            return parent::render($this->row);
        }
    }

    /**
     * @param array $action
     */
    private function updateLink($action)
    {
        /** @var \Magento\Framework\Phrase $caption */
        $caption = $action['caption'] ?? '';
        if ($caption instanceof \Magento\Framework\Phrase) {
            $actionCaption = $caption->getText();
        }
        $this->_transformActionData($action, $actionCaption, $this->row);
        $url = $action['href'] ?? '';
        if (!$this->startLink) {
            $this->html .= '<span class="action-divider"> | </span>';
        } else {
            $this->startLink = false;
        }
        $class = strtolower($actionCaption) . '-budget';
        $this->html .= '<a href="javascript:void(0)" class="' . $class
            . '" data-url="' . $url . '" data-id="' . $this->row->getId() . '">'
            . $actionCaption . '</a>';
    }
}
