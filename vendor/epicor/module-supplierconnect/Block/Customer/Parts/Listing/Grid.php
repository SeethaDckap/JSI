<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Customer\Parts\Listing;


/**
 * Parts list grid config
 * 
 * Note: columns for this grid are configured in the Magento Admin: Configuration > Supplier Connect
 * 
 * @category   Epicor
 * @package    Epicor_Supplierconnect
 * @author     Epicor Websales Team
 */
class Grid extends \Epicor\Common\Block\Generic\Listing\SearchArray
{
    const FRONTEND_RESOURCE_DETAIL = 'Epicor_Supplier::supplier_parts_details';
    /**
     * @var \Epicor\Common\Helper\Access
     */
    protected $commonAccessHelper;

    /**
     * @var \Epicor\Supplierconnect\Helper\Data
     */
    protected $supplierconnectHelper;
    
    /** 
     * @var type \Magento\Framework\Url\EncoderInterface
     */
    protected $urlEncoder;
    
    /**
     * @var type \Magento\Framework\Encryption\EncryptorInterface
     */
    
    protected $encryptor;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Common\Model\Message\CollectionArrayFactory $commonMessageCollectionFactory,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
        \Epicor\Common\Model\GridConfigOptionsModelReader $configOptionsModelReader,
        \Epicor\Common\Block\Generic\Listing\ColumnRendererReader $columnRendererReader,
        \Epicor\Common\Helper\Access $commonAccessHelper,
        \Epicor\Supplierconnect\Helper\Data $supplierconnectHelper,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        array $data = []
    )
    {
        $this->commonAccessHelper = $commonAccessHelper;
        $this->supplierconnectHelper = $supplierconnectHelper;
        $this->urlEncoder = $urlEncoder;
        $this->encryptor = $encryptor;
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

        $this->setId('supplierconnect_parts');
        $this->setDefaultSort('product_code');
        $this->setDefaultDir('desc');
        $this->setMessageBase('supplierconnect');
        $this->setMessageType('spls');
        $this->setIdColumn('product_code');
        $this->initColumns();
    }

    public function getRowUrl($row)
    {
        $url = null;
        $accessHelper = $this->commonAccessHelper;
        if ($this->isRowUrlAllowed() && $accessHelper->customerHasAccess('Epicor_Supplierconnect', 'Parts', 'details', '', 'Access')) {
            $helper = $this->supplierconnectHelper;
            $erp_account_number = $helper->getSupplierAccountNumber();
            $partDetails = array(
                'erp_account' => $erp_account_number,
                'product_code' => $row->getId(),
                'operational_code' => $row->getOperationalCode(),
                'effective_date' => $row->getEffectiveDate(),
                'unit_of_measure_code' => $row->getUnitOfMeasureCode()
            );
            
            $requested = $this->urlEncoder->encode($this->encryptor->encrypt(serialize($partDetails)));
            $url = $this->getUrl('*/*/details', array('part' => $requested));
        }

        return $url;
    }
}