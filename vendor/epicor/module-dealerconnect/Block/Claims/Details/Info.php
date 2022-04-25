<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Claims\Details;


/**
 * RFQ details - non-editable info block
 * 
 * @category   Epicor
 * @package    Epicor_Dealerconnect
 * @author     Epicor Websales Team
 */
class Info extends \Epicor\Dealerconnect\Block\Claims\Details\Template
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Dealerconnect\Helper\Messaging
     */
    protected $dealerconnectHelper;
    
    /**
     * @var \Magento\Framework\Url\EncoderInterface
     */
    protected $urlEncoder;
    
    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $_encryptor;

    /**
     * @var \Epicor\Comm\Model\Erp\Mapping\Claimstatus
     */
    protected $_claimStatusMapping;

    protected $_editStatuses = [
        'request',
        'open'
    ];

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Epicor\Dealerconnect\Helper\Messaging $dealerconnectHelper,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Epicor\Comm\Model\Erp\Mapping\Claimstatus $claimStatusMapping,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->dealerconnectHelper = $dealerconnectHelper;
        $this->urlEncoder = $urlEncoder;
        $this->_encryptor  = $encryptor;
        $this->formKey = $formKey;
        $this->_claimStatusMapping = $claimStatusMapping;
        parent::__construct(
            $context,
            $data
        );
    }


    public function _construct()
    {
        parent::_construct();

        $this->setTemplate('Epicor_Dealerconnect::claims/details/info.phtml');
        $this->setTitle(__('Information'));
    }

    public function _toHtml()
    {
        $claim = $this->registry->registry('dealer_connect_claim_details');
        $html = '';
        $helper = $this->dealerconnectHelper;
        $arr = $helper->varienToArray($claim);
        $claim = base64_encode(serialize($arr));
        $html = '<input type="hidden" name="old_claim_data" value="' . $claim . '" />';

        $html .= parent::_toHtml();
        return $html;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function registry($key)
    {
        return $this->registry->registry($key);
    }

    /**
     * @param $key
     * @param $value
     * @param bool $graceful
     */
    public function register($key, $value, $graceful = false)
    {
        $this->registry->register($key, $value, $graceful);
    }

    /**
     * @param $key
     */
    public function unregister($key)
    {
        $this->registry->unregister($key);
    }
    //M1 > M2 Translation End
    
    /**
     * Get the Return Url of a Claim
     * 
     * @return string
     */
    public function getErpReturnsUrl()
    {
        $claim = $this->registry->registry('dealer_connect_claim_details');
        $erpAccountNum = $this->dealerconnectHelper->getErpAccountNumber();
        $returnDetails = array(
            'erp_account' => $erpAccountNum,
            'erp_returns_number' => $claim->getErpReturnsNumber()
        );
        $claimDetails = array(
            'erp_account' => $erpAccountNum,
            'case_number' => $claim->getCaseNumber()
        );
        $requested = $this->urlEncoder->encode($this->_encryptor->encrypt(serialize($returnDetails)));
        $claimRequested = $this->urlEncoder->encode($this->_encryptor->encrypt(serialize($claimDetails)));
        $backUrl = $this->getUrl('*/*/details', array('claim' => $claimRequested));
        $claimUrlEncoded = $this->urlEncoder->encode($backUrl);
        $url = $this->getUrl('customerconnect/returns/details', array('return' => $requested, 'back' => $claimUrlEncoded));
        return $url;        
    }

    public function getBomUrl()
    {
        $claim = $this->registry->registry('dealer_connect_claim_details');
        $url = $this->getUrl('*/*/billOfMaterials', array('location' => $claim->getLocationNumber()));
        return $url;
    }
    
    /**
     * get form key
     *
     * @return string
     */
    public function getFormKey(){
        return $this->formKey->getFormKey();
    }

    /**
     * @return bool
     */
    public function _isFormAccessAllowed()
    {
        $allowed = true;
        $action = $this->getRequest()->getActionName();
        switch($action) {
            case 'new':
            case 'duplicate':
                $allowed = $this->_isAccessAllowed(static::FRONTEND_RESOURCE_CREATE);
                break;
            case 'details':
                $allowed = $this->_isAccessAllowed(static::FRONTEND_RESOURCE_EDIT);
                break;
        }

        if ($allowed == true) {
            $allowed = $this->canEditClaim();
        }

        return $allowed;
    }

    /**
     * @return bool
     */
    public function canEditClaim()
    {
        $claim = $this->registry->registry('dealer_connect_claim_details');
        $status = $claim->getStatus();
        if ($status != '' && $status == 'CLOSED') {
            return false;
        }
        $claimStatus = $claim->getClaimStatus();
        if (!is_null($claimStatus)) {
            $editErpStatusCode = $this->_claimStatusMapping
                ->getClaimStatus($this->_editStatuses)
                ->getData();
            $_editStatusCode = array_column($editErpStatusCode, 'erp_code');
            if (!empty($_editStatusCode)
                && !in_array($claimStatus, $_editStatusCode)
            ) {
                return false;
            }
        }
        return true;
    }
}
