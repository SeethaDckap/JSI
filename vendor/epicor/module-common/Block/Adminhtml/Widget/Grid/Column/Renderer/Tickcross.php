<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Widget\Grid\Column\Renderer;


class Tickcross extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * Render country grid column
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $data = $row->getData($this->getColumn()->getIndex());

        $mode = $this->getColumn()->getTickMode() ?: 'boolean';
        
        $displayTick = false;

        if ($mode == 'empty') {
            if (empty($data)) {
                $displayTick = true;
            }
        } else if ($mode == 'content') {
            if (!empty($data)) {
                $displayTick = true;
            }
        } else{
            $displayTick = filter_var($data, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        }
        if($row->getData()){
            $contactCode = array_key_exists("contact_code", $row->getData()) ? $row->getData()['contact_code'] : "null";
            $loginId = array_key_exists("login_id", $row->getData()) ? $row->getData()['login_id'] : "null";
            if($contactCode == "" && $loginId == ""){    
                $displayTick = true;
            } 
        }

        $output = '';
        if ($displayTick) {
            $output = ' <img src="' . $this->getViewFileUrl('Epicor_Common::epicor/common/images/success_msg_icon.gif') . '" alt="Yes" /> ';
        } else {
            if ($row->getSource() != 1) {     // don't display cross if ECC only
                $output = ' <img src="' . $this->getViewFileUrl('Epicor_Common::epicor/common/images/cancel_icon.gif') . '" alt="No" /> ';
            }
        }

        return $output;
    }

}
