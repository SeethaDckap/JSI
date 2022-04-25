<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Customer\Returns\Attachment\Lines\Renderer;


/**
 * Description of File
 *
 * @author Paul.Ketelle
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

        $index = $this->getColumn()->getIndex();
        $value = $this->escapeHtml($row->getDescription());

        $return = $this->registry->registry('return_model');
        /* @var $return Epicor_Comm_Model_Customer_ReturnModel */

        $link = $return->getAttachmentLink($row->getId());
        /* @var $link Epicor_Comm_Model_Customer_ReturnModel_Attachment */

        $disabled = ($link && $link->getToBeDeleted() == 'Y') ? ' disabled="disabled"' : '';

        $allowed = ($return) ? $return->isActionAllowed('Attachments') : true;

        if (!$this->registry->registry('review_display') && $allowed) {
            $html = '<input type="text" name="attachments[existing][' . $row->getUniqueId() . '][' . $index . ']" value="' . $value . '" class="attachments_' . $index . '"' . $disabled . '/>';
        } else {

            if ($link && $link->getToBeDeleted() == 'Y') {
                $html = __('To Be Deleted') . ' : ' . $value;
            } else {
                $html = $value;
            }
        }

        return $html;
    }

}
