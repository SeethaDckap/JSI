<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

//M1 > M2 Translation Begin (Rule 49)
namespace Epicor\Common\Model;


class Url extends \Magento\Framework\Url
{
    public function parseUrl($url)
    {
        return parent::_parseUrl($url);
    }
}
//M1 > M2 Translation End