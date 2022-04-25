<?php

namespace Epicor\Comm\Api;
/*
 * interface for configurator check
 */


interface ConfiguratorCheckInterface
{


    /**
     * @param integer $id
     * @param string $sku
     * @return mixed
     */
    public function configuratorCheck($id, $sku);
}
