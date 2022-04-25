<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Claims\Listing\Renderer;


/**
 * Serial number display
 *
 * @author     Epicor Websales Team
 */
class Action extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    
    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var \Magento\Framework\Url\EncoderInterface
     */
    protected $urlEncoder;
    
    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;
    
    /**
     * 
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        array $data = []
    ) {
        $this->encryptor = $encryptor;
        $this->urlEncoder = $urlEncoder;
        $this->commMessagingHelper = $commMessagingHelper;
        parent::__construct(
            $context,
            $data
        );
    }
    public function render(\Magento\Framework\DataObject $row)
    {
        $html = '';
        $erpAccountNum = $this->commMessagingHelper->getErpAccountNumber();
        $claimDetails = array(
            'erp_account' => $erpAccountNum,
            'case_number' => $row->getCaseNumber()
        );
            $requested = $this->urlEncoder->encode($this->encryptor->encrypt(serialize($claimDetails)));
            $url = $this->getUrl('*/*/details', array('claim' => $requested));
        $html = '<a href="' . $url . '">' . __('View/Edit') . '</a>';
        return $html;
    }

}
