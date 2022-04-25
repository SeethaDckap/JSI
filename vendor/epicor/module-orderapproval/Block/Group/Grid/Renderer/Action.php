<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Block\Group\Grid\Renderer;

use \Epicor\OrderApproval\Model\GroupSave\Utilities as GroupUtilites;

class Action extends \Epicor\Common\Block\Adminhtml\Widget\Grid\Column\Renderer\Action
{
    /**
     * @var GroupUtilites
     */
    private $utilities;

    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    private $accessauthorization;

    /**
     * Action constructor.
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param GroupUtilites $utilities
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        GroupUtilites $utilities,

        array $data = []
    ) {
        parent::__construct($context, $jsonEncoder, $data);
        $this->utilities = $utilities;
        $this->accessauthorization = $context->getAccessAuthorization();
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $actions = $this->getColumn()->getActions();
        if (empty($actions) || !is_array($actions)) {
            return '&nbsp;';
        }

        if ($this->getColumn()->getLinks() == true) {
            $html = '';
            foreach ($actions as $action) {
                if (is_array($action)) {
                    if(!$this->isActionAllowed($action, $row)){
                        continue;
                    }

                    if (isset($action['conditions'])) {
                        unset($action['conditions']);
                    }
                    if ($html != '') {
                        $html .= '<span class="action-divider">' . ($this->getColumn()->getDivider() ?: ' | ') . '</span>';
                    }
                    $html .= $this->_toLinkHtml($action, $row);
                }
            }
            return $html;
        } else {
            return parent::render($row);
        }
    }

    /**
     * @param $action
     * @param $row
     * @return bool
     */
    private function isActionAllowed($action, $row)
    {
        $actionType = $action['type'] ?? '';
        if (!$this->isEditableByCustomer($row) && $actionType === 'view') {
            return true;
        }
        $edit = $this->accessauthorization->isAllowed('Epicor_Customer::my_account_group_edit');
        $details = $this->accessauthorization->isAllowed('Epicor_Customer::my_account_group_details');
        if ($this->isEditableByCustomer($row) && $edit && in_array($actionType, ['edit'])) {
            return true;
        }
        if ($this->isEditableByCustomer($row) && !$edit && $details && in_array($actionType, ['view'])) {
            return true;
        }

        if ($this->isEditableByCustomer($row) && in_array($actionType, ['delete'])) {
            return true;
        }

        return false;
    }

    /**
     * @param $row
     * @return bool
     */
    private function isEditableByCustomer($row)
    {
        $customerEmail = $this->utilities->getCustomer()->getEmail();
        $groupCreatedBy = $row->getCreatedBy();

        return $customerEmail === $groupCreatedBy;
    }
}