<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
//M1 > M2 Translation Begin (Rule 46)

namespace Epicor\Common\Model;


class MessageRequestModelReader
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
    public function getModel($messageBase, $messageType)
    {
        return $this->readers[$messageBase. '_'. strtolower($messageType)]->create();
    }
}

//M1 > M2 Translation End