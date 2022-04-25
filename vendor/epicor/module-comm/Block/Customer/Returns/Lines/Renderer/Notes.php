<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Customer\Returns\Lines\Renderer;


class Notes extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        $this->registry = $registry;
        parent::__construct(
            $context,
            $data
        );
    }


    public function render(\Magento\Framework\DataObject $row)
    {

        $index = $this->getColumn()->getIndex();
        $comment = $row->getData($index);
        $notesLength = $this->scopeConfig->getValue('epicor_comm_returns/notes/line_notes_length', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $maxLength = $notesLength ? 'maxLength=' . $notesLength : '';
        $notesRequired = $this->scopeConfig->getValue('epicor_comm_returns/notes/line_notes_required', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if (!$this->registry->registry('review_display') && $row->isActionAllowed('Notes')) {
            $disabled = $row->getToBeDeleted() == 'Y' ? ' disabled="disabled"' : '';
            $html = '<textarea class="return_line_notes" ' . $maxLength . ' name="lines[' . $row->getUniqueId() . '][note_text]"' . $disabled . '>' . $this->escapeHtml($comment) . '</textarea>';
        } else {
            $html = '<textarea class="return_line_notes" ' . $maxLength . ' name="lines[' . $row->getUniqueId() . '][note_text]">' . $this->escapeHtml($comment) . '</textarea>';
        }
        if ($notesLength) {
            $html .= '<div id="truncated_message_line_notes">max ' . $notesLength . ' chars</div>';
        }
        return $html;
    }

}
