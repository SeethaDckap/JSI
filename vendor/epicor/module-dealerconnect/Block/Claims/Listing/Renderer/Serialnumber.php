<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Claims\Listing\Renderer;


/**
 * Serial number display
 *
 * @author     Epicor Websales Team
 */
class Serialnumber extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    public function render(\Magento\Framework\DataObject $row)
    {
        $html = "";
        $serialNumbers = $row->getData('serial_numbers')->getData('serial_number');
        if (count($serialNumbers) > 1) {
            $uniqueId = uniqid();
            foreach ($serialNumbers as $key => $serialNumber) {
                if ($key == 0) {
                    $html .= "<div class='expand-row'>"
                                . "<span style='margin-right:5px;'>".$serialNumber."</span>"
                                . "<span class='plus-minus' id='" .$uniqueId. "'>+</span>"
                                . "<span id='row-".$uniqueId."' style='display: none;'>"
                                . "<ul style='list-style:none; margin:0px; padding:0px'>";
                } else {
                    $html .= "<li>" .$serialNumber. "</li>";
                }
            }
            $html .= "</ul></span></div>";
        } else {
            $html = $serialNumbers;
        }
        return $html;
    }

}
