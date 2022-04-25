<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Block\Customer\Listing\Contracts\Listing;

/**
 * Customer Orders list Grid config
 * 
 * Note: columns for this grid are configured in the Magento Admin: Configuration > Customer Connect 
 * 
 * @category   Epicor
 * @package    Epicor_Supplierconnect
 * @author     Epicor Websales Team
 */
class Grid extends \Epicor\Common\Block\Generic\Listing\Search {

    /**
     * @var \Epicor\Common\Helper\Access
     */
    protected $commonAccessHelper;

    /**
     * @var \Epicor\Lists\Helper\Data
     */
    protected $listsHelper;

    /**
     * @var \Magento\Framework\Url\EncoderInterface
     */
    protected $urlEncoder;

    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $customerconnectHelper;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptor;

    public function __construct(
    \Magento\Backend\Block\Template\Context $context, \Magento\Backend\Helper\Data $backendHelper, \Epicor\Common\Model\Message\CollectionFactory $commonMessageCollectionFactory, \Epicor\Common\Helper\Data $commonHelper, \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper, \Epicor\Common\Model\GridConfigOptionsModelReader $configOptionsModelReader, \Epicor\Common\Block\Generic\Listing\ColumnRendererReader $columnRendererReader, \Epicor\Common\Helper\Access $commonAccessHelper, \Epicor\Lists\Helper\Data $listsHelper, \Magento\Framework\Url\EncoderInterface $urlEncoder, \Epicor\Customerconnect\Helper\Data $customerconnectHelper, \Magento\Framework\Encryption\EncryptorInterface $encryptor, array $data = []
    ) {
        $this->commonAccessHelper = $commonAccessHelper;
        $this->listsHelper = $listsHelper;
        $this->urlEncoder = $urlEncoder;
        $this->customerconnectHelper = $customerconnectHelper;
        $this->encryptor = $encryptor;
        parent::__construct(
                $context, $backendHelper, $commonMessageCollectionFactory, $commonHelper, $frameworkHelperDataHelper, $configOptionsModelReader, $columnRendererReader, $data
        );

        $this->setFooterPagerVisibility(true);
        $this->setId('customerconnect_customer_list_contracts_list_grid');
        $this->setDefaultSort('account_number');
        $this->setMessageBase('customerconnect');
        $this->setMessageType('cccs');
        $this->setIdColumn('account_number');
        $this->setSaveParametersInSession(false);

        $this->initColumns();
    }

    public function getRowUrl($row) {

        $url = null;
        $accessHelper = $this->commonAccessHelper;
        /* @var $helper Epicor_Common_Helper_Access */
        if ($accessHelper->customerHasAccess('Customerconnect', 'Contract', 'details', '', 'Access')) {
            $helper = $this->customerconnectHelper;
            /* @var $helper Epicor_Lists_Helper_Data */
            $erp_account_number = $helper->getErpAccountNumber();
            $contract = $this->urlEncoder->encode($this->encryptor->encrypt($erp_account_number . ']:[' . $row->getContractCode()));
            //   $contract = $helper->urlEncode($helper->encrypt($row->getContractCode()));
            $params = array('contract' => $contract);
            $url = $this->getUrl('*/*/details', $params);
//            $url = $this->getUrl('epicor_lists/contract/details', $params);
//            $erp_account_number = $helper->getErpAccountNumber();
//            $order_requested = $helper->urlEncode($helper->encrypt($erp_account_number . ']:[' . $row->getId()));
//            $url = $this->getUrl('*/*/details', array('order' => $order_requested));
        }

        return $url;
    }

    protected function initColumns() {
        parent::initColumns();

        $columns = $this->getCustomColumns();

        $this->setCustomColumns($columns);
    }

}
