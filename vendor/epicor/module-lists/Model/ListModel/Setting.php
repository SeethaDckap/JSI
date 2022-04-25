<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Model\ListModel;


/**
 * Model Class for List Settings
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Setting
{

    const LIST_SETTING_MANDATORY = 'M';
    const LIST_SETTING_FORCE = 'F';
    const LIST_SETTING_DEFAULT = 'D';
    const LIST_SETTING_QUICKORDERPAD = 'Q';
    const LIST_SETTING_EXCLUDE = 'E';

    private $settings = array(
        self::LIST_SETTING_MANDATORY => 'Mandatory: The list must always be used',
        self::LIST_SETTING_FORCE => 'Force: Customer forced to select 1 list with this flag',
        self::LIST_SETTING_DEFAULT => 'Default: Automatically assigned to Customer',
        self::LIST_SETTING_QUICKORDERPAD => 'Quick Order Pad: Auto load for the quick order pad',
        self::LIST_SETTING_EXCLUDE => 'Exclude: The list is exclusive'
    );

    /**
     * Returns array of settings for use with select boxes
     *
     * @return array
     */
    public function toOptionArray($filter = array())
    {
        $settings = array();
        foreach ($this->settings as $value => $label) {
            if (empty($filter) || in_array($value, $filter)) {
                $settings[] = array('value' => $value, 'label' => $label);
            }
        }

        return $settings;
    }

    /**
     * Returns array of settings
     *
     * @return array
     */
    public function getSettings()
    {
        return $this->settings;
    }

}
