<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Returns\Listing;


/**
 * Customer RETURN list Grid config
 * 
 * Note: columns for this grid are configured in the Magento Admin: Configuration > Customer Connect
 * 
 * @category   Epicor
 * @package    Epicor_Supplierconnect
 * @author     Epicor Websales Team
 */
class Grid extends \Epicor\Common\Block\Generic\Listing\Search
{

    const FRONTEND_RESOURCE_EXORT = 'Epicor_Customerconnect::customerconnect_account_returns_export';

    const FRONTEND_RESOURCE_DETAIL = 'Epicor_Customerconnect::customerconnect_account_returns_details';

    /**
     * @var \Epicor\Common\Helper\Access
     */
    protected $commonAccessHelper;

    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $customerconnectHelper;
    
     /**
     * @var \Magento\Framework\Url\EncoderInterface
     */
    protected $urlEncoder;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $_encryptor ;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Common\Model\Message\CollectionFactory $commonMessageCollectionFactory,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
        \Epicor\Common\Model\GridConfigOptionsModelReader $configOptionsModelReader,
        \Epicor\Common\Block\Generic\Listing\ColumnRendererReader $columnRendererReader,
        \Epicor\Common\Helper\Access $commonAccessHelper,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        array $data = []

    )
    {
        $this->commonAccessHelper = $commonAccessHelper;
        $this->customerconnectHelper = $customerconnectHelper;
        $this->urlEncoder = $urlEncoder;
        $this->_encryptor  = $encryptor;
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
        $this->setId('customerconnect_returns');
        $this->setDefaultSort('id');
        $this->setDefaultDir('desc');
        $this->setMessageBase('epicor_comm');
        $this->setMessageType('crrs');
        $this->setIdColumn('erp_returns_number');
        $this->initColumns();
        $this->setExportTypeCsv(array('text' => 'CSV', 'url' => '*/*/exportCsv'));
        $this->setExportTypeXml(array('text' => 'XML', 'url' => '*/*/exportXml'));
    }

    public function getRowUrl($row)
    {

        $url = null;
        if ($this->isRowUrlAllowed()) {
            $helper = $this->customerconnectHelper;
            /* @var $helper Epicor_Customerconnect_Helper_Data */
            $erpAccountNum = $helper->getErpAccountNumber();

            $quoteDetails = array(
                'erp_account' => $erpAccountNum,
                'erp_returns_number' => $row->getErpReturnsNumber()
            );

            $requested = $this->urlEncoder->encode($this->_encryptor->encrypt(serialize($quoteDetails)));
            $url = $this->getUrl('*/*/details', array('return' => $requested));
        }

        return $url;
    }

}
