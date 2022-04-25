<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */


namespace Epicor\Punchout\Block\Adminhtml\Connections\Edit\Form\Element;

use Epicor\Comm\Helper\Data;
use Epicor\Comm\Helper\Messaging;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Escaper;
use Magento\Framework\UrlInterface;

/**
 * Class SecretKey
 */
class ErpAccount extends AbstractElement
{

    const AJAX_URL = 'adminhtml/epicorcomm_customer_erpaccount/listerpaccounts/';

    /**
     * Url interface.
     *
     * @var UrlInterface
     */
    private $url;

    /**
     * Comm Mesaging.
     *
     * @var Data
     */
    private $commHelper;


    /**
     * ErpAccount constructor.
     *
     * @param Factory           $factoryElement    Factory element.
     * @param CollectionFactory $factoryCollection Factory collection.
     * @param Escaper           $escaper           Escaper.
     * @param UrlInterface      $url               Url interface.
     * @param Messaging         $commHelper        Comm helper.
     * @param array             $data              Data array.
     */
    public function __construct(
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        UrlInterface $url,
        Messaging $commHelper,
        array $data=[]
    ) {
        $this->url        = $url;
        $this->commHelper = $commHelper;
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);

    }//end __construct()


    /**
     * Get Html element
     *
     * @return string
     */
    public function getElementHtml()
    {
        $value = $this->getErpAcctName() ?: '';
        return '<input type="hidden" name="account_type_no_label" id="'.$this->getHtmlId().'_no_account" 
                    value="'.__($value).'" />
                 <div class="admin__field-control" id="ecc_account_selector">
                 <input readonly type="text" data-form-part="connection_form" name="'.$this->getHtmlId().'_label"
                    id="'.$this->getHtmlId().'_label" value="'.__($value).'" class="erpaccount_label type_field _required required-entry"/>
                 <input type="hidden" name="selected_identity" id="selected_identity" value="'.$this->getValue().'" />
                <input type="hidden" name="'.$this->getHtmlId().'" id="'.$this->getHtmlId().'" 
                    value="'.__('customer').'" />
                <input type="hidden" name="customer_url" id="'.$this->getHtmlId().'_customer_url" 
                    value="'.$this->url->getUrl(self::AJAX_URL, ['punchout_filter' => 1]).'" />
                <input type="hidden" name="identity" 
                    id="'.$this->getHtmlId().'_account_id_customer" value="" class="type_field"/>
                <input type="hidden" name="customer_label" id="'.$this->getHtmlId().'_customer_label" value="" />
                <button class="form-button" id="'.$this->getHtmlId().'_trig" 
                    onclick="accountSelector.openpopup(\''.$this->getHtmlId().'\'); return false;">'.__('Select').'
                    </button></div>'.$this->getAfterElementHtml();

    }//end getElementHtml()


    /**
     * Render HTML for element's label
     *
     * @param string $idSuffix   ID suffix.
     * @param string $scopeLabel Socpe label.
     *
     * @return string
     */
    public function getLabelHtml($idSuffix='', $scopeLabel='')
    {
        $scopeLabel = $scopeLabel ? ' data-config-scope="'.$scopeLabel.'"' : '';

        if ($this->getLabel() !== null) {
            $html = '<div class="admin__field-label" 
            for="'.$this->getHtmlId().$idSuffix.'"'.$this->_getUiId(
                'label'
            ).'><label><span'.$scopeLabel.'>'.$this->_escape(
            $this->getLabel()
            ).'</label></span></div>'."\n";
        } else {
            $html = '';
        }

        return $html;

    }//end getLabelHtml()


    /**
     * Get the default html.
     *
     * @return mixed
     */
    public function getDefaultHtml()
    {
        $html     = $this->getData('default_html');
        $required = $this->getRequired() ? ' _required' : '';
        if ($html === null) {
            $html = $this->getNoSpan() === true ? '' : '<div class="admin__field'.$required.'">'."\n";
            $html .= $this->getLabelHtml();
            $html .= $this->getElementHtml();
            $html .= $this->getNoSpan() === true ? '' : '</div>'."\n";
        }

        return $html;

    }//end getDefaultHtml()


    /**
     * Get ERP Account Name
     *
     * @return string
     */
    public function getErpAcctName()
    {
        $erpCode    = $this->getValue();
        $erpAccount = $this->commHelper->getErpAccountByAccountNumber($erpCode, 'customer', true);

        return $erpAccount->getName();

    }//end getErpAcctName()


}//end class
