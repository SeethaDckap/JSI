<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Claims\Listing;


/**
 * Dealers Claim list Grid config
 * 
 * Note: columns for this grid are configured in the Magento Admin: Configuration > Dealer Connect
 * 
 * @category   Epicor
 * @package    Epicor_Dealerconnect
 * @author     Epicor Websales Team
 */
class Grid extends \Epicor\Common\Block\Generic\Listing\Search
{
    const FRONTEND_RESOURCE_DETAIL = 'Dealer_Connect::dealer_claim_details';

    /**
     * @var \Epicor\Common\Helper\Access
     */
    protected $commonAccessHelper;

    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;

    /**
     * @var \Epicor\Lists\Helper\Frontend\Contract
     */
    protected $listsFrontendContractHelper;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Magento\Framework\Url\EncoderInterface
     */
    protected $urlEncoder;

    /**
     * @var  \Magento\Framework\Url\DecoderInterface
     */
    protected $urlDecoder;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Common\Model\Message\CollectionFactory $commonMessageCollectionFactory,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
        \Epicor\Common\Model\GridConfigOptionsModelReader $configOptionsModelReader,
        \Epicor\Common\Block\Generic\Listing\ColumnRendererReader $columnRendererReader,
        \Epicor\Common\Helper\Access $commonAccessHelper,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        \Epicor\Lists\Helper\Frontend\Contract $listsFrontendContractHelper,
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\Framework\Url\DecoderInterface $urlDecoder,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\App\Request\Http $request,
        array $data = []
    )
    {
        $this->commonAccessHelper = $commonAccessHelper;
        $this->commMessagingHelper = $commMessagingHelper;
        $this->listsFrontendContractHelper = $listsFrontendContractHelper;
        $this->commHelper = $commHelper;
        $this->urlEncoder = $urlEncoder;
        $this->urlDecoder = $urlDecoder;
        $this->encryptor = $encryptor;
        $this->request = $request;
        parent::__construct(
            $context,
            $backendHelper,
            $commonMessageCollectionFactory,
            $commonHelper,
            $frameworkHelperDataHelper,
            $configOptionsModelReader,
            $columnRendererReader,
            $data
        );

        $this->setFooterPagerVisibility(true);
        $helper = $this->commonAccessHelper;

        $this->setId('dealerconnect_claims');
        $this->setDefaultSort('caseNumber');
        $this->setDefaultDir('desc');
        $this->setMessageBase('dealerconnect');
        $this->setMessageType('dcls');
        $this->setIdColumn('caseNumber');
        $this->initColumns();
        $this->setExportTypeCsv(array('text' => 'CSV', 'url' => '*/*/exportCsv'));
        $this->setExportTypeXml(array('text' => 'XML', 'url' => '*/*/exportXml'));
    }

    public function getRowUrl($row)
    {
        $url = null;
        if ($this->isRowUrlAllowed()) {
            $erpAccountNum = $this->commMessagingHelper->getErpAccountNumber();
            $claimDetails = array(
                'erp_account' => $erpAccountNum,
                'case_number' => $row->getCaseNumber(),
                'case_status' => $row->getStatus()
            );
            $requested = $this->urlEncoder->encode($this->encryptor->encrypt(serialize($claimDetails)));
            $url = $this->getUrl('*/*/details', array('claim' => $requested));
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

//        $columns['action'] = array(
//            'header' => __('Action'),
//            'type' => 'text',
//            'filter' => false,
//            'sortable' => false,
//            'renderer' => 'Epicor\Dealerconnect\Block\Claims\Listing\Renderer\Action',
//        );

        $this->setCustomColumns($columns);
    }

    protected function _toHtml()
    {
        $html = parent::_toHtml();
        return $html;
    }

    protected function _prepareCollection()
    {
        parent::_prepareCollection();

        $helper = $this->commHelper;

        $items = $this->getCollection()->getItems();
        if (is_array($items)) {
            foreach ($items as $item) {
                $helper->sanitizeData($item);
            }
        }
    }

    public function setCollection($collection)
    {
        $cases = $this->urlDecoder->decode($this->request->getParam('cases'));
        $caseNumbers = unserialize($this->encryptor->decrypt($cases));
        if(!empty($caseNumbers)){
            $dealerFilter = [
                'case_number' => $caseNumbers,
            ];
            $collection->setRowFilters($dealerFilter);
            $this->_collection = $collection;
        }else{
            return parent::setCollection($collection);
        }

    }


}
