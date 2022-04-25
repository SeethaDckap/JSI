<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Customer\Account\Dashboard;


/**
 * Customer Account Dashboard Block, used for back links
 * 
 * @author gareth.james
 */
class AbstractBlock extends \Magento\Customer\Block\Account\Dashboard
{

    protected $_defaultUrl;

    /**
     * @var \Epicor\Common\Helper\Data
     */
    protected $commonHelper;

    /**
     * @var \Magento\Framework\Url\DecoderInterface
     */
    protected $urlDecoder;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Api\AccountManagementInterface $customerAccountManagement,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Framework\Url\DecoderInterface $urlDecoder,
        array $data = []
    ) {
        $this->commonHelper = $commonHelper;
        $this->urlDecoder = $urlDecoder;
        parent::__construct(
            $context,
            $customerSession,
            $subscriberFactory,
            $customerRepository,
            $customerAccountManagement,
            $data
        );
    }


    /**
     * Get back url in account dashboard
     *
     * This method is copypasted in:
     * Mage_Wishlist_Block_Customer_Wishlist  - because of strange inheritance
     * Mage_Customer_Block_Address_Book - because of secure url
     *
     * @return string
     */
    public function getBackUrl()
    {
        $url = $this->getUrl($this->_defaultUrl);
        // the RefererUrl must be set in appropriate controller
        if ($this->getRefererUrl()) {

            if ($this->getRequest()->getParam('back')) {
                $url = $this->urlDecoder->decode($this->getRequest()->getParam('back'));
            } else {
                $url = $this->getRefererUrl();
            }
        }

        return $url;
    }

    /**
     * Get list url in account dashboard
     *
     * @return string
     */
    public function getListPageUrl()
    {
        $value = $this->getListUrl();

        if ($this->getRequest()->getParam('list_url')) {
            $value = $this->urlDecoder->decode($this->getRequest()->getParam('list_url'));
        }

        return $value;
    }

    /**
     * Get list url in account dashboard
     *
     * @return string
     */
    public function getListTypeVal()
    {
        $value = $this->getListType();

        if ($this->getRequest()->getParam('list_type')) {
            $value = $this->getRequest()->getParam('list_type');
        }

        return $value;
    }

}
