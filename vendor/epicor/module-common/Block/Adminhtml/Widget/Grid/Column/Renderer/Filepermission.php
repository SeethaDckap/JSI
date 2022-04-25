<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Widget\Grid\Column\Renderer;


/**
 * Filepermission grid column renderer. renders a file permission  in human readable format
 * 
 * @category   Epicor
 * @package    Epicor_Common
 * @author     Epicor Websales Team
 */
class Filepermission extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var \Epicor\Common\Helper\Data
     */
    protected $commonHelper;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Epicor\Common\Helper\Data $commonHelper,
        array $data = []
    ) {
        $this->commonHelper = $commonHelper;
        parent::__construct(
            $context,
            $data
        );
    }


    /**
     * Render action grid column
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $data = $row->getData($this->getColumn()->getIndex());
        $helper = $this->commonHelper;
        /* @var $helper Epicor_Common_Helper_Data */
        $log = $helper->urlEncode($helper->encrypt($row->getFilename()));
        $url = $this->getUrl('*/*/view', array('log' => $log));
        //return $this->getUrl('*/*/view', array('log' => $log));
        if ($data['status'] == "notreadable") {
            return '<span style="color:red">File not readable!</span>';
        } else if ($data['status'] == "notwritable") {
            return '<span style="color:red">File not writable!</span>';
        } else {
            return "<a  href='" . $url . "'>View</a>";
        }
    }

}
