<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Customer\Returns\Lines\Renderer;


/**
 * Return line attachments column renderer
 * 
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Attachments extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Common\Helper\File
     */
    protected $commonFileHelper;
    /*
     * @var \Magento\Framework\App\State
     */
    protected $appState;


    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Registry $registry,
        \Epicor\Common\Helper\File $commonFileHelper,
        \Magento\Framework\App\State $appState,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->commonFileHelper = $commonFileHelper;
        $this->appState = $appState;
        parent::__construct(
            $context,
            $data
        );
    }


    public function render(\Magento\Framework\DataObject $row)
    {
        /* @var $row Epicor_Comm_Model_Customer_ReturnModel_Line */
        if (!$this->registry->registry('review_display')) {

            $html = '</td>'
                . '</tr>'
                . '<tr class="lines_row attachment" id="row_return_line_attachments_' . $row->getUniqueId() . '" style="display:none">'
                . '<td colspan="10" class="attachments-row">';

            if ($this->registry->registry('current_return_line')) {
                $this->registry->unregister('current_return_line');
            }

            $this->registry->register('current_return_line', $row);

            $block = $this->getLayout()->createBlock('\Epicor\Comm\Block\Customer\Returns\Lines\Attachments');
            /* @var $block Epicor_Comm_Block_Customer_Returns_Lines_Attachments */

            $html .= $block->toHtml();
        } else {
            $attachments = $row->getAttachments();
            if (!empty($attachments)) {
                $html = '';
                $deleted = '';
                $helper = $this->commonFileHelper;
                /* @var $helper Epicor_Common_Helper_File */
                foreach ($attachments as $attachment) {
                    /* @var $attachment Epicor_Common_Model_File */

                    $link = $row->getAttachmentLink($attachment->getId());
                    /* @var $link Epicor_Comm_Model_Customer_ReturnModel_Attachment */
                    if ((!$link || $link->getToBeDeleted() != 'Y') && $row->getToBeDeleted() != 'Y') {
                        $url = "";
                        $originalArea = $this->appState->getAreaCode();
                        if($originalArea == 'adminhtml'){
                            $url = $helper->getFileUrl($attachment->getId(), $attachment->getErpId(), $attachment->getFilename(), $attachment->getUrl(),'', true);
                        }else{
                            $url = $helper->getFileUrl($attachment->getId(), $attachment->getErpId(), $attachment->getFilename(), $attachment->getUrl());
                        }

                        $html .= '<p>' . $attachment->getFilename() . ' <a href="' . $url . '" target="_blank">View</a></p>';
                    } else {
                        $deleted .= '<p>' . $attachment->getFilename() . '</p>';
                    }
                }

                if ($deleted != '') {
                    $html .= '<p>' . __('To Be Deleted') . ':</p>';
                    $html .= $deleted;
                }
            } else {
                $html = __('No Attachments');
            }
        }

        return $html;
    }

}
