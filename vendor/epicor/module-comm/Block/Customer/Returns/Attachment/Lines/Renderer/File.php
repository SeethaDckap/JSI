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
class File extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var \Epicor\Common\Helper\File
     */
    protected $commonFileHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    /*
     * @var \Magento\Framework\App\State
     */
    protected $appState;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Epicor\Common\Helper\File $commonFileHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\State $appState,
        array $data = []
    ) {
        $this->commonFileHelper = $commonFileHelper;
        $this->registry = $registry;
        $this->appState = $appState;
        parent::__construct(
            $context,
            $data
        );
    }


    public function render(\Magento\Framework\DataObject $row)
    {
        /* @var $row Epicor_Common_Model_File */

        $helper = $this->commonFileHelper;
        /* @var $helper Epicor_Common_Helper_File */

        $index = $this->getColumn()->getIndex();
        $url = "";
        $originalArea = $this->appState->getAreaCode();
        if($originalArea == 'adminhtml'){
            $url = $helper->getFileUrl($row->getId(), $row->getErpId(), $row->getFilename(), $row->getUrl(),'', true);
        }else{
            $url = $helper->getFileUrl($row->getId(), $row->getErpId(), $row->getFilename(), $row->getUrl());
        }

        $html = $row->getFilename() . '<a href="' . $url . '" target="_blank" class="attachment_view" style="padding-left:8px">View</a>';

        $return = $this->registry->registry('return_model');
        /* @var $return Epicor_Comm_Model_Customer_ReturnModel */

        $link = $return->getAttachmentLink($row->getId());
        /* @var $link Epicor_Comm_Model_Customer_ReturnModel_Attachment */

        $disabled = ($link && $link->getToBeDeleted() == 'Y') ? ' disabled="disabled"' : '';

        $allowed = ($return) ? $return->isActionAllowed('Attachments') : true;

        if (!$this->registry->registry('review_display') && $allowed) {
            $html .= ' | ' . __('Update File') . ': <input type="file" name="attachments[existing][' . $row->getUniqueId() . '][' . $index . ']" class="attachments_' . $index . '" ' . $disabled . '/>';
        }

        return $html;
    }

}
