<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
/**
 * Syslog grid item renderer
 * 
 */

namespace Epicor\Common\Block\Adminhtml\Advanced\Syslog\Column\Renderer;

class Viewdownload extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Text {

    /**
     * @var \Epicor\Common\Helper\Data
     */
    protected $encoder;
    protected $encryptor;

    /**
     * 
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\Url\EncoderInterface $encoder
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Url\EncoderInterface $encoder,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        array $data = []
    ) {
        $this->encoder = $encoder;
        $this->encryptor = $encryptor;
        parent::__construct(
                $context, $data
        );
    }

    /**
     * Renders grid column
     *
     * @param \Magento\Framework\DataObject $row
     * @return mixed
     */
    public function _getValue(\Magento\Framework\DataObject $row) {

        $pos = strpos($row->getFilename(), 'WSO-6196-');
        if ($pos !== false) {
            return '<a href="' . $this->getUrl(
                            '*/*/download', ['filename' => base64_encode($row->getData('name'))]
                    ) . '"/>' . 'Download';
        } else {
            $log = $this->encoder->encode($row->getFilename());
            $url = $this->getUrl('*/*/view', array('filename' => base64_encode($row->getData('name'))));
            $downloadurl = $this->getUrl('*/*/download', array('filename' => base64_encode($row->getData('name'))));
            return "<a  href='" . $url . "'>View</a><span class='action-divider'> | </span><a  href='" . $downloadurl . "'>Download</a>";
        }
    }

}
