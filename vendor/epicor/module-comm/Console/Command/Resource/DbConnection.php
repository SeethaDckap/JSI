<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Console\Command\Resource;


class DbConnection extends \Magento\Framework\App\ResourceConnection
{

    public function getDbConnection($config)
    {
        return $this->connectionFactory->create($config);
    }

}