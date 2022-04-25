<?php
/**
 * Copyright © 2010-2019 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Model;


class Cron
{
    /**
     * @var \Epicor\Dealerconnect\Model\Claimstatus
     */
    protected $_claimStatus;
    /**
     * @var \Epicor\Dealerconnect\Model\DealerReminder
     */
    protected $_claimsReminder;

    /**
     * @var \Epicor\Dealerconnect\Helper\Data
     */
    protected $_dealerHelper;

    /**
     * Cron constructor.
     * @param \Epicor\Dealerconnect\Model\Claimstatus $claimStatus
     */
    public function __construct(
        \Epicor\Dealerconnect\Model\Claimstatus $claimStatus,
        \Epicor\Dealerconnect\Model\DealerReminder $claimsReminder,
        \Epicor\Dealerconnect\Helper\Data $dealerHelper
    )
    {
        $this->_claimStatus = $claimStatus;
        $this->_claimsReminder = $claimsReminder;
        $this->_dealerHelper = $dealerHelper;
    }

    /**
     * Updates the Claim Status Data for Dashboard
     *
     * @return void
     */
    public function updateClaimsStatusData()
    {
        if (!$this->_dealerHelper->claimStatusDataMappingExists()) {
            $this->_claimStatus->clearData();
            return;
        }
        if ($_claimStatusData = $this->_claimStatus->updateClaimsStatus()) {
            $this->_claimStatus->saveClaimStatusData($_claimStatusData);
            $this->_claimsReminder->checkAndSendReminder($_claimStatusData);
        }
        return;
    }

    /**
     * Sending Expiry Email
     *
     * @return void
     */

    public function checkExpiryReminder()
    {
        $this->_claimsReminder->checkExpiryReminder();
        return;
    }
}