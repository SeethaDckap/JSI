<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Adminhtml\Arpayments\View\Tab;

/**
 * Order information tab
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 100.0.2
 */
class Info extends \Magento\Backend\Block\Widget implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;
    
    protected $assetRepo;
    
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = [])
    {
        $this->_coreRegistry = $registry;
        $this->assetRepo  = $context->getAssetRepository();
        parent::__construct($context, $data);
    }
    
    /**
     * Retrieve order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_ar_order');
    }

    /**
     * Retrieve source model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getSource()
    {
        return $this->getOrder();
    }

    /**
     * Retrieve order totals block settings
     *
     * @return array
     */
    public function getOrderTotalData()
    {
        return [
            'can_display_total_due' => true,
            'can_display_total_paid' => true,
            'can_display_total_refunded' => true
        ];
    }

    /**
     * Get order info data
     *
     * @return array
     */
    public function getOrderInfoData()
    {
        return ['no_use_order_link' => true];
    }

    /**
     * Get tracking html
     *
     * @return string
     */
    public function getTrackingHtml()
    {
        return $this->getChildHtml('order_tracking');
    }

    /**
     * Get items html
     *
     * @return string
     */
    public function getItemsHtml()
    {
        return $this->getChildHtml('ar_order_items');
    }

    /**
     * Retrieve gift options container block html
     *
     * @return string
     */
    public function getGiftOptionsHtml()
    {
        return $this->getChildHtml('gift_options');
    }

    /**
     * Get payment html
     *
     * @return string
     */
    public function getPaymentHtml()
    {
        return $this->getChildHtml('ar_order_payment');
    }

    /**
     * View URL getter
     *
     * @param int $orderId
     * @return string
     */
    public function getViewUrl($arpaymentId)
    {
        return $this->getUrl('adminhtml/arpayment/view', ['arpayment_id' => $arpaymentId]);
    }

    /**
     * ######################## TAB settings #################################
     */

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Information');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('AR Payment Information');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }
    
    public function getPaymentInfo()
    {
        return $this->getOrder()->getPayment();
    }
    public function getCardType()
    {
        $cardType = $this->getPaymentInfo()->getCcType();
        $imagePath = 'Epicor_Common::epicor/common/images/cardtypes/' . strtolower($cardType) . '.gif';
        $imageUrl = $this->getViewFileUrl($imagePath);
        return '<img src="' . $imageUrl . '" alt="' . $cardType . '"/>';
    }

    public function getCardNumber()
    {
        return $this->getPaymentInfo()->getCcLast4();
    }

    public function getAvsStatus()
    {
        return $this->getPaymentInfo()->getCcAvsStatus() ?: 'N/A';
    }

    public function getCvvStatus()
    {
       return $this->getPaymentInfo()->getEccCcCvvStatus() ?: 'N/A';
    }

    public function getExpiryDate()
    {
        return $this->getPaymentInfo()->getCcExpMonth().' / '.$this->getPaymentInfo()->getCcExpYear();
    }
    
    public function getEccCcvToken()
    {
        return $this->getPaymentInfo()->getEccCcvToken();
    }

    public function getEccCvvToken()
    {
        return $this->getPaymentInfo()->getEccCvvToken();
    }    
    
    public function getEsdmLogo()
    {
       $createAsset = $this->assetRepo->createAsset('Epicor_Esdm::images/esdm.jpg');
       return $createAsset->getUrl();
    }      
    
    public function getArpaymentMethod() {
        $methods = $this->getPaymentInfo()->getMethod();
        return $methods;
    }    
    
    public function getArpaymentTitle() {
        $methods = $this->getPaymentInfo()->getAdditionalInformation();
        $title = $methods['method_title'];
        return $title;
    } 
    
    public function getLastTransId() {
        $methods = $this->getPaymentInfo()->getLastTransId();
        return $methods;
    }     
}
