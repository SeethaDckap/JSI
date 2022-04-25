<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Block\Account\Manage\Erpaccounts\Renderer;


/**
 * Invoice Reorder link grid renderer
 * 
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Salesrepaccount extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var \Epicor\SalesRep\Helper\Account\Manage
     */
    protected $salesRepAccountManageHelper;

    /**
     * @var \Epicor\SalesRep\Model\ResourceModel\Account\CollectionFactory
     */
    protected $salesRepResourceAccountCollectionFactory;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Epicor\SalesRep\Helper\Account\Manage $salesRepAccountManageHelper,
        \Epicor\SalesRep\Model\ResourceModel\Account\CollectionFactory $salesRepResourceAccountCollectionFactory,
        array $data = []
    ) {
        $this->salesRepAccountManageHelper = $salesRepAccountManageHelper;
        $this->salesRepResourceAccountCollectionFactory = $salesRepResourceAccountCollectionFactory;
        parent::__construct(
            $context,
            $data
        );
    }


    public function render(\Magento\Framework\DataObject $row)
    {
        $helper = $this->salesRepAccountManageHelper;
        /* @var $helper Epicor_SalesRep_Helper_Manage */

        $currentAccount = $this->getColumn()->getCurrentAccount();
        /* @var $currentAccount Epicor_SalesRep_Model_Account */

        $accountsIds = $this->getColumn()->getAccountChildrenIds();
        $accountsIds[] = $currentAccount->getId();

        $salesRepAccounts = $this->salesRepResourceAccountCollectionFactory->create();
        $salesRepAccounts->join(array('erp' => 'ecc_salesrep_erp_account'), 'main_table.id = erp.sales_rep_account_id', '');
        $salesRepAccounts->addFieldToFilter('erp.erp_account_id', $row->getEntityId());
        $salesRepAccounts->addFieldToFilter('main_table.id', array('in' => $accountsIds));

        $thisAccount = false;
        $accountNames = array();
        foreach ($salesRepAccounts as $account) {
            if ($account->getId() == $currentAccount->getId()) {
                $thisAccount = true;
            } else {
                //M1 > M2 Translation Begin (Rule 55)
                //$accountNames[] = __('Child account: %s', $account->getName());
                $accountNames[] = __('Child account: %1', $account->getName());
                //M1 > M2 Translation End
            }
        }

        if ($thisAccount) {
            array_unshift($accountNames, __('This account'));
        }

        $html = '';
        if (count($accountNames) == 1) {
            $html = array_pop($accountNames);
        } elseif (count($accountNames) > 1) {
            $divId = 'salesrepaccounts-' . $row->getId();
            $jsCode = "\$('$divId').style.display=\$('$divId').style.display==''?'none':'';window.event.stopPropagation()||(window.event.cancelBubble=true);";
            //$jsCode = "javascript:if(\$('$divId').visible()){\$('$divId').hide()}else{\$('$divId').show()}";
            $html = __('Multiple accounts');
            $html .= ' <a href="javascript:void(0)" title="' . __('Show/Hide') . '" onclick="' . $jsCode . '">' . __('Show/Hide') . '</a>';
            $html .= '<div id="' . $divId . '" style="display: none">' . implode('<br />', $accountNames) . '</div>';
        }

        return $html;
    }

}
