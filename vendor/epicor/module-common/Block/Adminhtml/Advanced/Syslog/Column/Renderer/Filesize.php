<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
/**
 * Syslog grid item renderer
 * 
 */
namespace Epicor\Common\Block\Adminhtml\Advanced\Syslog\Column\Renderer;

class Filesize extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Text
{

    protected $_ftp;

    public function __construct(
    \Magento\Framework\System\Ftp $ftp
    )
    {
        $this->_ftp = $ftp;       
    }

    /**
     * Renders grid column
     *
     * @param \Magento\Framework\DataObject $row
     * @return mixed
     */
    public function _getValue(\Magento\Framework\DataObject $row)
    {
        if ($row->getData('size') === 0) {
            return (string)$row->getData('size');
        } else {
            return $this->_ftp->byteconvert($row->getData('size'));
        }
    }
}
