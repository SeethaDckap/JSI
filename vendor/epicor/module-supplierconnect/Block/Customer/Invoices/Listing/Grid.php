<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Customer\Invoices\Listing;


/**
 * Supplier Invoices list Grid config
 * 
 * Note: columns for this grid are configured in the Magento Admin: Configuration > Supplier Connect
 * 
 * @category   Epicor
 * @package    Epicor_Supplierconnect
 * @author     Epicor Websales Team
 */
class Grid extends \Epicor\Common\Block\Generic\Listing\SearchArray
{
    const FRONTEND_RESOURCE_DETAIL = 'Epicor_Supplier::supplier_invoices_details';
    /**
     * @var \Epicor\Common\Helper\Access
     */
    protected $commonAccessHelper;

    /**
     * @var \Epicor\Supplierconnect\Helper\Data
     */
    protected $supplierconnectHelper;

    /**
     * @var \Magento\Framework\Url\EncoderInterface
     */
    protected $urlEncoder;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;

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
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        array $data = []
    )
    {
        $this->commonAccessHelper = $commonAccessHelper;
        $this->supplierconnectHelper = $supplierconnectHelper;
        $this->urlEncoder = $urlEncoder;
        $this->encryptor = $encryptor;
        $this->commMessagingHelper = $commMessagingHelper;
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

        $this->setId('supplierconnect_invoices');
        $this->setDefaultSort('invoice_number');
        $this->setDefaultDir('desc');
        $this->setMessageBase('supplierconnect');
        $this->setMessageType('suis');
        $this->setIdColumn('invoice_number');
        $this->initColumns();
    }

    public function getRowUrl($row)
    {
        $url = null;
        if ($this->isRowUrlAllowed()){
            $accessHelper = $this->commonAccessHelper;
            /* @var $helper \Epicor\Common\Helper\Access */

            $msgHelper = $this->commMessagingHelper;
            $enabled = $msgHelper->isMessageEnabled('supplierconnect', 'suid');

            if ($enabled && $accessHelper->customerHasAccess('Epicor_Supplierconnect', 'Invoices', 'details', '', 'Access')) {
                $helper = $this->supplierconnectHelper;
                /* @var $helper Epicor\Supplierconnect\Helper\Data */
                $erp_account_number = $helper->getSupplierAccountNumber();
                $invoice = [
                    $erp_account_number,
                    $row->getId()
                ];
                $requested = $this->urlEncoder->encode($this->encryptor->encrypt(serialize($invoice)));
                $url = $this->getUrl('*/*/details', array('invoice' => $requested));
            }
        }
        return $url;
    }

}
