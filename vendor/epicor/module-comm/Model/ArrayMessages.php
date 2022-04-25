<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
//M1 > M2 Translation Begin (Rule 46)

namespace Epicor\Comm\Model;


class ArrayMessages
{
    protected $readers;

    public function __construct(
        $readers
    )
    {
        $this->readers = $readers;
    }

    /**
     * @param $messageBase
     * @param $messageType
     * @return \Epicor\Comm\Model\Message\Request
     */
    public function getType()
    {
        return $this->readers;
    }
}

//M1 > M2 Translation End