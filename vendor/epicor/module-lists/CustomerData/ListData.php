<?php

/**
 * Copyright © 2010-2020 Epicor Software. All rights reserved.
 */

namespace Epicor\Lists\CustomerData;

/**
 * Class ListData
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class ListData
{
    /**
     * @var string
     */
    private $listTags;

    /**
     * @return string
     */
    public function getListTags()
    {
        return $this->listTags;
    }

    /**
     * @param string $listTags
     */
    public function setListTags($listTags)
    {
        $this->listTags = $listTags;
    }
}