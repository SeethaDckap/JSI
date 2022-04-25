<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Elements\Model\Config\Source;


class Avsresults
{

    public function toOptionArray()
    {
        return array(
            array('value' => 'A', 'label' => "A - Address Match (For Discover, Address & Zipcode Match"),
            array('value' => 'B', 'label' => "B - Incompatible format for postal information"),
            array('value' => 'C', 'label' => "C - Incompatible format for address & postal information"),
            array('value' => 'D', 'label' => __('D - Address & postal codes match')->render()),
            array('value' => 'E', 'label' => "E - AVS not allowed for this transaction"),
            array('value' => 'F', 'label' => __("F - International Transaction: Address and postal code match")->render()),
            array('value' => 'G', 'label' => "G - Global non-AVS participant"),
            array('value' => 'I', 'label' => "I - International Transaction: Address not verified for international transaction"),
            array('value' => 'J', 'label' => "J - American Express only. Card member & ship-to verified - Fraud Protection Program"),
            array('value' => 'K', 'label' => "K - American Express only. Card member & ship-to verified - Standard"),
            array('value' => 'M', 'label' => __("M - Address & postal codes Match")->render()),
            array('value' => 'N', 'label' => "N - No Match"),
            array('value' => 'P', 'label' => __("P - Postal codes match")->render()),
            array('value' => 'R', 'label' => "R - System unavailable or timed out"),
            array('value' => 'S', 'label' => "S - Service not supported"),
            array('value' => 'T', 'label' => "T - Nine-digit zipcode matches"),
            array('value' => 'U', 'label' => "U - Unavailable"),
            array('value' => 'W', 'label' => "W - Nine-digit zipcode matches"),
            array('value' => 'X', 'label' => "X - Address & nine-digit zipcode match"),
            array('value' => 'Y', 'label' => "Y - Address & five-digit zipcode match (For Discover, no data provided)"),
            array('value' => 'Z', 'label' => "Z - Five-digit zipcode matches"),
            array('value' => '0', 'label' => "0 - No address verification has been requested (TSYS oly)"),
        );
    }

}
