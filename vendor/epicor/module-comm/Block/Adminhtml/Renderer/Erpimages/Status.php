<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Renderer\Erpimages;


/**
 * ERP Image status renderer
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Status extends \Magento\Backend\Block\AbstractBlock
{
    
    public function _generateHtml(\Magento\Framework\DataObject $row)
    { 
        $html ='';
        $rawStatus =   $row->getStatus(); 
        $status = '';
        if ($rawStatus == '0') {
            $status = 'Not-Synced';
        } else if ($rawStatus == '1') {
            $status = 'Synced';
        } else {
            $status = 'Error: ' . $rawStatus;
        }

        $html .= $status;

        return $html;
    }

}
