<?php
/**
 * Copyright © 2020 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Sales\Invoice\Email\Sender;

/**
 * Class OrderSender
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SouInvoice implements \Epicor\Comm\Api\Data\SouInvoiceInterface
{
    /**
     * Sou Invoice Email Flag.
     *
     * @var bool
     */
    private $flag;

    /**
     * Get Sou Invoice Email Flag.
     * @return bool
     */
    public function getSouInvoiceEmailFlag()
    {
        return $this->flag;
    }

    /**
     * Set Sou Invoice Email Flag
     *
     * @param $flag bool
     * @return mixed|void
     */
    public function setSouInvoiceEmailFlag($flag = false)
    {
        $this->flag = $flag;

    }
}
