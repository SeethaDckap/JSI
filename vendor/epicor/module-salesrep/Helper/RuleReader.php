<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */


namespace Epicor\SalesRep\Helper;


class RuleReader
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
    public function getRule($rule)
    {
        return $this->readers[$rule];
    }
}