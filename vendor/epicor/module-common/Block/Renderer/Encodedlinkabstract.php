<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Renderer;


use Epicor\Customerconnect\Helper\ExportFile as ExportFileHelper;

/**
 * Description of Encodedlinkabstract
 *
 * @author Paul.Ketelle
 */
class Encodedlinkabstract extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    protected $_path = '*/*/*';
    protected $_key = 'key';
    protected $_accountType = 'customer';
    protected $_addBackUrl = false;
    protected $_customParams = array();
    protected $_permissions = \Epicor\AccessRight\Acl\RootResource::FRONTEND_RESOURCE;
    protected $_showLink = true;

    /**
     * @var \Epicor\Common\Helper\Access
     */
    protected $commonAccessHelper;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Magento\Framework\Url\EncoderInterface
     */
    protected $urlEncoder;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptor;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Epicor\Common\Helper\Access $commonAccessHelper,
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        array $data = []
    ) {
        $this->commonAccessHelper = $commonAccessHelper;
        $this->commHelper = $commHelper;
        $this->urlEncoder = $urlEncoder;
        $this->encryptor = $encryptor;
        $this->_accessauthorization = $context->getAccessAuthorization();
        parent::__construct(
            $context,
            $data
        );
    }

    private function disableLinkForExportFile()
    {
        if ($this->isExportAction()) {
            $this->_showLink = false;
        }
    }

    public function isExportAction(): bool
    {
        return ExportFileHelper::isExportAction(
            $this->getRequest()->getActionName(),
            $this->getRequest()->getModuleName()
        );
    }

    public function render(\Magento\Framework\DataObject $row)
    {

        $link = '';

        $id = $row->getData($this->getColumn()->getIndex());
        $this->disableLinkForExportFile();
        if ($this->_showLink && $this->_isAccessAllowed($this->_permissions)) {

            $helper = $this->commHelper;

            if ($this->_accountType == 'customer') {
                $erp_account_number = $helper->getErpAccountNumber();
            } else if ($this->_accountType == 'supplier') {
                $erp_account_number = $helper->getSupplierAccountNumber();
            }


            $item_requested = $this->urlEncoder->encode($this->encryptor->encrypt($erp_account_number . ']:[' . $id));
            $params = array($this->_key => $item_requested);

            if ($this->_addBackUrl) {
                $params['back'] = $this->urlEncoder->encode($this->getUrl('*/*/*', $this->getRequest()->getParams()));
            }

            if (!empty($this->_customParams)) {
                foreach ($this->_customParams as $key => $val) {
                    if (strpos($key, '_url') !== false) {
                        $val = $this->urlEncoder->encode($this->getUrl($val));
                    }

                    $params[$key] = $val;
                }
            }

            $url = $this->getUrl($this->_path, $params);

            if (!empty($id)) {
                $link = '<a href="' . $url . '" >' . $id . '</a>';
            }
        } else {
            if (!empty($id)) {
                $link = $id;
            }
        }

        return $link;
    }

    /**
     * @return bool
     */
    protected function _isAccessAllowed($code)
    {
        return $this->_accessauthorization->isAllowed($code);
    }

}
