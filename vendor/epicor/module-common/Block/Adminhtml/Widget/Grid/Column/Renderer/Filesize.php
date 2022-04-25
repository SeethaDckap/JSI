<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Common\Block\Adminhtml\Widget\Grid\Column\Renderer;

/**
 * Filesize grid column renderer. renders a file size in human readable format
 * 
 * @category   Epicor
 * @package    Epicor_Common
 * @author     Epicor Websales Team
 */
class Filesize extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
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
            return (string) $row->getData('size');
        } else {
            return $this->_ftp->byteconvert($row->getData('size'));
        }
    }

}
