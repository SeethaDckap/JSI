<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Silk\CustomAccount\Block\Customer\Invoices\Listing;

use Epicor\Customerconnect\Helper\ExportFile as ExportFileHelper;
use Epicor\Customerconnect\Model\EccHidePrices\HidePrice as HidePrice;
use Magento\Framework\DataObjectFactory as DataObject;

class Grid extends \Epicor\Customerconnect\Block\Customer\Invoices\Listing\Grid
{

    private $hidePrice;
    private $dataObjectFactory;

    public function __construct(
        HidePrice $hidePrice,
        DataObject $dataObjectFactory,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Common\Model\Message\CollectionFactory $commonMessageCollectionFactory,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
        \Epicor\Common\Model\GridConfigOptionsModelReader $configOptionsModelReader,
        \Epicor\Common\Block\Generic\Listing\ColumnRendererReader $columnRendererReader,
        \Epicor\Common\Helper\Access $commonAccessHelper,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Epicor\Lists\Helper\Frontend\Contract $listsFrontendContractHelper,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        array $data = []
    ) {
        parent::__construct(
            $hidePrice,
            $dataObjectFactory,
            $context,
            $backendHelper,
            $commonMessageCollectionFactory,
            $commonHelper,
            $frameworkHelperDataHelper,
            $configOptionsModelReader,
            $columnRendererReader,
            $commonAccessHelper,
            $customerconnectHelper,
            $listsFrontendContractHelper,
            $urlEncoder,
            $encryptor,
            $data
        );

        $this->hidePrice = $hidePrice;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    public function getRowUrl($row)
    {
        $url = null;
        if ($this->isRowUrlAllowed()) {
            $helper = $this->customerconnectHelper;
            $erpAccountNumber = $helper->getErpAccountNumber();
            $invoice = $this->urlEncoder->encode($this->encryptor->encrypt($erpAccountNumber . ']:[' . $row->getId()));
            $params = array('invoice' => $invoice, 'attribute_type' => $row->get_attributesType());
            $url = $this->getUrl('customerconnect/invoices/details', $params);
        }

        return $url;
    }

    private function isExportAction(): bool
    {
        return ExportFileHelper::isExportAction(
            $this->getRequest()->getActionName(),
            $this->getRequest()->getModuleName()
        );
    }
}
