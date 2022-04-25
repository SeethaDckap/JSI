<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Block\Adminhtml\Listing\Edit\Tab\Restrictions\Renderer;


/**
 * List Restricted address renderer
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Action extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Action
{

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $backendAuthSession;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        array $data = []
    ) {
        $this->backendAuthSession = $backendAuthSession;
        parent::__construct(
            $context,
            $jsonEncoder,
            $data
        );
    }


    public function render(\Magento\Framework\DataObject $row)
    {
        $rowId = $row->getId();
        $actions = $this->getColumn()->getActions();
        if (empty($actions) || !is_array($actions)) {
            return '&nbsp;';
        }
        $id = $this->getRequest()->getParam('list_id');
        $restrictionType = $this->backendAuthSession->getRestrictionTypeValue();
        if ($this->getColumn()->getLinks() == true) {
            $html = '';
            foreach ($actions as $action) {
                if ($action['caption'] == 'Edit') {
                    $action['style'] = "cursor:pointer;";
                    $action['onclick'] = "openRestrictionForm($rowId,$id,'edit','" . $restrictionType . "');";
                }
                if ($action['caption'] == 'Delete') {
                    $action['style'] = "cursor:pointer;";
                    $action['onclick'] = "deleteRestrictedAddress($rowId,'" . $restrictionType . "')";
                }

                if (is_array($action)) {
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

}
