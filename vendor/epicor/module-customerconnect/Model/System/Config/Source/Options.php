<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Model\System\Config\Source;


/**
 * 
 * Customer Account Config Source Options
 * 
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Options
{
    /**
     * @var \Epicor\Comm\Model\GlobalConfig\Config
     */
    protected $globalConfig;

    public function __construct(
        \Epicor\Comm\Model\GlobalConfig\Config $globalConfig
    )
    {
        $this->globalConfig = $globalConfig;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {

        //M1 > M2 Translation Begin (Rule 1)
        //$typeNode = (array) Mage::getConfig()->getNode('global/my_account_menu');
        $typeNode = (array) $this->globalConfig->get('my_account_menu');
        //M1 > M2 Translation End
        $types = array();

        foreach ($typeNode as $type => $info) {

            $types[$type] = (array) $info;
        }
        return $types;
    }

}
