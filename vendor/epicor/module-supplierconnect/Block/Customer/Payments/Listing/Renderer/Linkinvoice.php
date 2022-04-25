<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Customer\Payments\Listing\Renderer;


/**
 * Purchase Invoice link display
 *
 * @author Gareth.James
 */
class Linkinvoice extends \Epicor\Common\Block\Renderer\Encodedlinkabstract
{

    protected $_path = 'supplierconnect/invoices/details';
    protected $_key = 'invoice';
    protected $_accountType = 'supplier';
    protected $_addBackUrl = true;
    protected $_permissions = "Epicor_Supplier::supplier_invoices_details";

    public function render(\Magento\Framework\DataObject $row)
    {

        $link = '';

        $id = $row->getData($this->getColumn()->getIndex());

        if ($this->_showLink && $this->_isAccessAllowed($this->_permissions)) {

            $helper = $this->commHelper;

            if ($this->_accountType == 'customer') {
                $erp_account_number = $helper->getErpAccountNumber();
            } else if ($this->_accountType == 'supplier') {
                $erp_account_number = $helper->getSupplierAccountNumber();
            }


            $item_requested = $this->urlEncoder->encode($this->encryptor->encrypt(serialize([$erp_account_number, $id])));
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
}
