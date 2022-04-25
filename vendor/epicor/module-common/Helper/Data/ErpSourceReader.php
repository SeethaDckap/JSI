<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */


namespace Epicor\Common\Helper\Data;


class ErpSourceReader
{
    protected $readers;

    public function __construct(
        $readers
    )
    {
        $this->readers = $readers;
    }

    /**
     * @param $source
     * @return mixed
     */
    public function getModel($source)
    {
        if (isset($this->readers[$source])) {
            return $this->readers[$source];
        } else {
            throw new \InvalidArgumentException('Model '.$source.' not found.');
        }
    }
}