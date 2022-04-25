<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
//M1 > M2 Translation Begin (Rule p2-7)

namespace Epicor\Common\Helper;


class FunctionReader
{
    protected $readers;

    public function __construct(
        $readers
    )
    {
        $this->readers = $readers;
    }

    /**
     * @param $helper
     * @return mixed
     */
    public function getHelper($helper)
    {
        return $this->readers[$helper];
    }
}
//M1 > M2 Translation End