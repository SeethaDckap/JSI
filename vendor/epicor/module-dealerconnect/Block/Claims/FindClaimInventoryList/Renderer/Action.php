<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Claims\FindClaimInventoryList\Renderer;


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
        $url = $this->getUrl('*/*/findclaimdetail', array('location_num' =>$row->getLocationNumber() 
                ,'account_num' =>$row->getLocationAddressAccountNumber()));

//            $requested = $this->urlEncoder->encode($this->encryptor->encrypt(serialize($quoteDetails)));
//            $url = $this->getUrl('*/*/quotedetails', array('quote' => $requested));
        $html = '<a href="' . $url . '" onclick="dealerClaim.claimDetail(this);return false;">' .__('Claim') . '</a>';
        return $html;
    }

}
