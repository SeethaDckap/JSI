<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
//M1 > M2 Translation Begin (Rule 46)

namespace Epicor\Common\Model;


class AccountTypeModelReader
{
    protected $readers;

    public function __construct(
        $readers
    )
    {
        $this->readers = $readers;
    }

    /**
     * @param $modelName
     * @return mixed
     */
    public function getModel($modelName)
    {
        return $this->readers[$modelName];
    }
}
//M1 > M2 Translation End