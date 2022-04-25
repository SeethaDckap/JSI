<?php
/**
 * Copyright © 2020 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Api\Data;

/**
 * Sou Invoice Email Flag interface.
 */
interface SouInvoiceInterface
{


    /**
     * Get Sou Invoice Email Flag
     *
     * @return bool
     */
    public function getSouInvoiceEmailFlag();


    /**
     * Set Sou Invoice Email Flag
     *
     * @param bool $flag
     * @return bool
     */
    public function setSouInvoiceEmailFlag($flag);
}
