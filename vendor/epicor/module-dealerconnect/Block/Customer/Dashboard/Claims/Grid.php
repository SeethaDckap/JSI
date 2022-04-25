<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Customer\Dashboard\Claims;


/**
 * Customer Claims list Grid config
 *
 * @category   Epicor
 * @package    Epicor_Dealerconnect
 * @author     Epicor Websales Team
 */
class Grid extends \Epicor\Common\Block\Customer\Dashboard\Grid
{

    const FRONTEND_RESOURCE_DETAIL = "Dealer_Connect::dealer_claim_details";

    protected $dashboardSection = 'dealer_dashboard_claims';

    protected $_defaultSort = 'case_number';

    protected $id = 'dealerconnect_recent_claims';

    protected $messageBase = 'dealerconnect';

    protected $messageType = 'dcls';

    protected $idColumn = 'case_number';

    protected $entityType = 'Claims';

    protected $_defaultDateFilter = 'create_on_date';

    protected $_statusFilter = [
        'status'=> 'status',
        'value' => 'OPEN'
    ];

    public function getRowUrl($row)
    {
        $url = null;
        if ($this->isRowUrlAllowed()) {
            $helper = $this->customerconnectHelper;
            $erp_account_number = $helper->getErpAccountNumber();
            $claimDetails = array(
                'erp_account' => $erp_account_number,
                'case_number' => $row->getCaseNumber()
            );
            $backUrl = $this->getUrl('dealerconnect/dashboard/index');
            $pos = strpos($backUrl, "?");
            if ($pos !== false){
                $backUrl = substr($backUrl, 0, $pos);
            }
            $back = $this->urlEncoder->encode($backUrl);
            $requested = $this->urlEncoder->encode($this->encryptor->encrypt(serialize($claimDetails)));
            $url = $this->getUrl('*/claims/details', array('claim' => $requested, 'back' => $back));
        }

        return $url;
    }

    protected function initColumns()
    {
        parent::initColumns();

        $columns = $this->getCustomColumns();

        if ($this->listsFrontendContractHelper->contractsDisabled()) {
            unset($columns['contracts_contract_code']);
        }

        $this->setCustomColumns($columns);
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/dashboard/claims');
    }
}
