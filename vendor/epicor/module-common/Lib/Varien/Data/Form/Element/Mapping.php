<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Common\Lib\Varien\Data\Form\Element;

/**
 * Mapping table array renderer, used for quick start to display mapping tabgles in editable format
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Mapping extends AbstractArray
{

    protected function getColumns()
    {
        return $this->getMappingFields();
    }

    protected function getRowData()
    {
        return $this->getValues();
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setType('mapping');
    }
}