<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
//M1 > M2 Translation Begin (Rule 46)

namespace Epicor\Common\Helper;


class GenericgridReader
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
     * @return \Epicor\Common\Helper\Genericgrid
     */
    public function getHelper($messageBase)
    {
        if(isset($messageBase))
        {
            return $this->readers[$messageBase];
        }
    }
}

//M1 > M2 Translation End