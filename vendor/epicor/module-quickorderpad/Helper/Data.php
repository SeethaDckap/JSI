<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\QuickOrderPad\Helper;


class Data extends \Epicor\Common\Helper\Data
{

    /**
     * Retrieve quick order pad url
     *
     * @return string
     */
    public function getQuickorderpadUrl()
    {
        return $this->_getUrl('quickorderpad/form');
    }

}
