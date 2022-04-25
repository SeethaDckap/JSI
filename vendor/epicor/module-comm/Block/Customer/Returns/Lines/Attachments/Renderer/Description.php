<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Customer\Returns\Lines\Attachments\Renderer;


/**
 * Return line attachments description
 * 
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Description extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->registry = $registry;
        parent::__construct(
            $context,
            $data
        );
    }


    public function render(\Magento\Framework\DataObject $row)
    {
        /* @var $row Epicor_Common_Model_File */

        $line = $this->registry->registry('current_return_line');
        /* @var $line Epicor_Comm_Model_Customer_ReturnModel_Line */

        $index = $this->getColumn()->getIndex();
        $value = $this->escapeHtml($row->getDescription());

        $link = $line->getAttachmentLink($row->getId());
        /* @var $link Epicor_Comm_Model_Customer_ReturnModel_Attachment */

        $disabled = ($link->getToBeDeleted() == 'Y' || $line->getToBeDeleted() == 'Y' ) ? ' disabled="disabled"' : '';

        if (!$this->registry->registry('review_display') && $line->isActionAllowed('Attachments')) {
            $html = '<input type="text" name="lineattachments[existing][' . $line->getUniqueId() . '][' . $row->getUniqueId() . '][' . $index . ']" value="' . $value . '" class="line_attachments_' . $index . '"' . $disabled . '/>';
        } else {
            $html = $value;
        }
        return $html;
    }

}
