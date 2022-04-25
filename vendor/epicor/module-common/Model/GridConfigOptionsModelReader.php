<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
//M1 > M2 Translation Begin (Rule 46)

namespace Epicor\Common\Model;


class GridConfigOptionsModelReader
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
        if (isset($this->readers[$modelName])) {
            return $this->readers[$modelName];
        } else {
            throw new \InvalidArgumentException('Ivalid model name - '.$modelName);
        }
    }
}

//M1 > M2 Translation End