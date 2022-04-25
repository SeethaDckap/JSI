<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */


namespace Epicor\Reports\Helper;


class ChartReader
{

    protected $readers;

    public function __construct(
        $readers
    )
    {
        $this->readers = $readers;
    }

    /**
     * @param $chart
     * @return mixed
     */
    public function getChart($chart)
    {
        return $this->readers[$chart];
    }
}