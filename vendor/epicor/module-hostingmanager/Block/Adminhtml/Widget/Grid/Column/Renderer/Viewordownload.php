<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\HostingManager\Block\Adminhtml\Widget\Grid\Column\Renderer;

use \Epicor\Common\Controller\Adminhtml\Download\Log;
use Epicor\Common\Model\LogView;

/**
 * grid column renderer. renders a human readable/download format
 * 
 * @category   Epicor
 * @package    Epicor_Common
 * @author     Epicor Websales Team
 */
class Viewordownload extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var \Epicor\Common\Helper\Data
     */
    protected $encoder;
    
    protected $encryptor;

    private $logView;

    /**
     * Viewordownload constructor.
     * @param LogView $logView
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\Url\EncoderInterface $encoder
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param array $data
     */
    public function __construct(
        LogView $logView,
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Url\EncoderInterface $encoder,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        array $data = []
    ) {
        $this->encoder = $encoder;
        $this->encryptor =$encryptor;
        parent::__construct(
            $context,
            $data
        );
        $this->logView = $logView;
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
        /* @var $helper Epicor_Common_Helper_Data */
        
        $log = $this->encoder->encode($row->getFilename());
        $url = $this->getUrl('*/*/view', array('log' => $log));
        $downloadurl = $this->getUrl('*/*/download', array('log' => $log));        
        if (isset($data['status']) && $data['status'] == "notreadable") {
            return '<span style="color:red">File not readable!</span>';
        } else if (isset($data['status']) && $data['status'] == "notwritable") {
            return '<span style="color:red">File not writable!</span>';
        } else {
            return $this->logView->getViewDownLoad($row->getFilename(), 'nginx');
        }
    }
}
