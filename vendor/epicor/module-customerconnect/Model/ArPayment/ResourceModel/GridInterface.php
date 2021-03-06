<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Model\ArPayment\ResourceModel;

/**
 * @api
 * Interface GridInterface
 * @since 100.0.2
 */
interface GridInterface
{
    /**
     * Adds new rows to the grid.
     *
     * Only rows that correspond to $value and $field parameters should be added.
     *
     * @param int|string $value
     * @param null|string $field
     * @return \Zend_Db_Statement_Interface
     */
    public function refresh($value, $field = null);

    /**
     * Adds new rows to the grid.
     *
     * Only rows created/updated since the last method call should be added.
     *
     * @return \Zend_Db_Statement_Interface
     */
    public function refreshBySchedule();

    /**
     * @param int|string $value
     * @param null|string $field
     * @return int
     */
    public function purge($value, $field = null);
}
