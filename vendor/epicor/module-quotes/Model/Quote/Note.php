<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Quotes\Model\Quote;


/**
 * 
 * @method int getId()
 * @method int getQuoteId()
 * @method int setQuoteId(int $value)
 * @method int getAdminId()
 * @method int setAdminId(int $value)
 * @method string getNote()
 * @method string setNote(string $value)
 * @method string getCreatedAt()
 * @method string setCreatedAt(string $value)
 * @method bool getIsPrivate()
 * @method bool setIsPrivate(bool $value)
 * @method bool getIsVisible()
 * @method bool setIsVisible(bool $value)
 */
class Note extends \Epicor\Database\Model\Quote\Note
{

    const STATE_FOR_LATER = 1;
    const STATE_PUBLISH_NOW = 2;
    const STATE_PRIVATE = 3;

    protected $_admin;

    /**
     * @var \Magento\User\Model\UserFactory
     */
    protected $userUserFactory;

    /**
     * @var \Epicor\Quotes\Model\QuoteFactory
     */
    protected $quotesQuoteFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * //@var \Magento\Framework\TranslateInterface
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $translateInterface;

    /**
     * @var \Magento\Email\Model\TemplateFactory
     */
    //protected $emailTemplateFactory;
    /**
     * //@var \Magento\Email\Model\TemplateFactory
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var \Epicor\Quotes\Helper\Data
     */
    protected $quotesHelper;

    /**
     * @var \Magento\Framework\Url
     */
    protected $urlBuilder;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\User\Model\UserFactory $userUserFactory,
        \Epicor\Quotes\Model\QuoteFactory $quotesQuoteFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Translate\Inline\StateInterface $translateInterface,
       // \Magento\Email\Model\TemplateFactory $emailTemplateFactory,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Epicor\Quotes\Helper\Data $quotesHelper,
        \Magento\Framework\Url $urlBuilder,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->userUserFactory = $userUserFactory;
        $this->quotesQuoteFactory = $quotesQuoteFactory;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->translateInterface = $translateInterface;
        //$this->emailTemplateFactory = $emailTemplateFactory;
        $this->transportBuilder = $transportBuilder;
        $this->quotesHelper = $quotesHelper;
        $this->urlBuilder = $urlBuilder;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }


    public function _construct()
    {
        $this->_init('Epicor\Quotes\Model\ResourceModel\Quote\Note');
    }

    /**
     * 
     * @return \Magento\User\Model\User
     */
    public function getAdmin()
    {
        if (!$this->_admin) {
            $this->_admin = $this->userUserFactory->create()->load($this->getAdminId());
        }

        return $this->_admin;
    }

    /**
     * 
     * @return bool
     */
    public function isAdminNote()
    {
        return (bool) $this->getAdminId();
    }

    /**
     * 
     * @return bool
     */
    public function isCustomerNote()
    {
        return !$this->isAdminNote();
    }

    public function beforeSave()
    {
        parent::beforeSave();
        if (!$this->getCreatedAt()) {
            $this->setCreatedAt(time());
        }
    }

    public function afterSave()
    {
        parent::afterSave();

        if ($this->getSendEmail()) {
            if ($this->hasAdminId() && $this->getIsVisible() && !$this->getIsPrivate()) {
                $this->sendCustomerUpdate();
            } elseif (!$this->hasAdminId()) {
                $this->sendAdminUpdate();
            }
        }
    }

    /**
     * Gets the Quote for this note
     * 
     * @return \Epicor\Quotes\Model\Quote
     */
    public function getQuote()
    {
        if (!$this->hasData('quote')) {
            $quoteId = $this->getQuoteId();
            $quote = $this->quotesQuoteFactory->create()->load($quoteId);
            $this->setData('quote', $quote);
        }
        return $this->getData('quote');
    }

    public function sendCustomerUpdate()
    {
        $gqr_no_email_to_customer = $this->scopeConfig->getValue('epicor_comm_enabled_messages/gqr_request/submit_to_customer', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $enabled = $this->scopeConfig->isSetFlag('epicor_quotes/email_alerts/send_customer_note_emails', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($enabled && $this->getQuote()->getSendCustomerComments() && $gqr_no_email_to_customer == 'N') {

            $storeId = $this->getQuote()->getStoreId();
            $from = $this->scopeConfig->getValue('epicor_quotes/email_alerts/customer_note_email_address', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
            $sender = [
                'name' => $this->scopeConfig->getValue('trans_email/ident_' . $from . '/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId),
                'email' => $this->scopeConfig->getValue('trans_email/ident_' . $from . '/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId),
            ];
            $to = $this->getQuote()->getCustomer(true)->getEmail();
            $name = $this->getQuote()->getCustomer(true)->getName();

            $translate = $this->translateInterface;
            /* @var $translate Mage_Core_Model_Translate */
            $translate->suspend(false);
            $routeParams = ['id' => $this->getQuote()->getId(), '_nosid' => true, '_scope' => $storeId];
            $vars = array(
                'name' => $name,
                'epicquote' => $this->getQuote(),
                'epicquotereference' => $this->getQuote()->getReference() ?: $this->getQuote()->getId(),
                'lastcomment' => nl2br($this->getNote()),
                //M1 > M2 Translation Begin (Rule p2-4)
                //'myQuotesUrl' => Mage::getUrl('quotes/manage/view', array('id' => $this->getQuote()->getId()))
                'myQuotesUrl' => $this->urlBuilder->getUrl('quotes/manage/view', $routeParams),
                //M1 > M2 Translation End
            );
            /* // dprecated code for Magento 1 sending transaction mail //
            $this->emailTemplateFactory->create()
                ->setDesignConfig(array('area' => 'frontend', 'store' => $storeId))
                ->sendTransactional(
                    $this->scopeConfig->getValue('epicor_quotes/email_alerts/customer_note_email_template', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId), $setting, $to, $name, $vars
            );
            */
             try {
                $template = $this->scopeConfig->getValue('epicor_quotes/email_alerts/customer_note_email_template', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
                $templateOptions = array('area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId);

                $mail = $this->transportBuilder->setTemplateIdentifier($template)
                                ->setTemplateOptions($templateOptions)
                                ->setTemplateVars($vars)
                                ->setFrom($sender)
                                ->addTo($to)
                                ->getTransport();
                $mail->sendMessage();
                $translate->resume(true);
               
             } catch (\Exception $e) {
                  $translate->resume(true);
             }
        }
    }

    public function sendAdminUpdate()
    {
        $gqr_no_email_to_customer = $this->scopeConfig->getValue('epicor_comm_enabled_messages/gqr_request/submit_to_customer', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $enabled = $this->scopeConfig->isSetFlag('epicor_quotes/email_alerts/send_admin_note_emails', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($enabled && $this->getQuote()->getSendAdminComments() && $gqr_no_email_to_customer == 'N') {
            $customerGroup = $this->getQuote()->getCustomerGroup(true);
            $expires = $this->quotesHelper->getHumanExpires($this->getQuote());

            $storeId = $this->getQuote()->getStoreId();
            $from = $this->scopeConfig->getValue('epicor_quotes/email_alerts/admin_note_email_address', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
            $sender = [
                'name' => $this->scopeConfig->getValue('trans_email/ident_' . $from . '/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId),
                'email' => $this->scopeConfig->getValue('trans_email/ident_' . $from . '/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId),
            ];
            $to = $this->scopeConfig->getValue('trans_email/ident_' . $from . '/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
            $name = $this->scopeConfig->getValue('trans_email/ident_' . $from . '/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);

            $translate = $this->translateInterface;
            /* @var $translate Mage_Core_Model_Translate */
            $translate->suspend(false);
            $customer = $this->getQuote()->getCustomer(true);
            $vars = array(
                'adminname' => $name,
                'epicquote' => $this->getQuote(),
                'epicquotereference' => $this->getQuote()->getReference() ?: $this->getQuote()->getId(),
                'customer' => $customer,
                'expires' => $expires,
                'customerGroupName' => $customerGroup->getName(),
                'lastcomment' => nl2br($this->getNote()),
            );

            /* // dprecated code for Magento 1 sending transaction mail //
            $this->emailTemplateFactory->create()
                ->setDesignConfig(array('area' => 'frontend', 'store' => $storeId))
                ->sendTransactional(
                    $this->scopeConfig->getValue('epicor_quotes/email_alerts/admin_note_email_template', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId), $setting, $to, $name, $vars
            );
            */
            try {
                $template = $this->scopeConfig->getValue('epicor_quotes/email_alerts/admin_note_email_template', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
                $templateOptions = array('area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId);

                $mail = $this->transportBuilder->setTemplateIdentifier($template)
                                ->setTemplateOptions($templateOptions)
                                ->setTemplateVars($vars)
                                ->setFrom($sender)
                                ->addTo($to)
                                ->getTransport();
                $mail->sendMessage();
                $translate->resume(true);
               
             } catch (\Exception $e) {
                  $translate->resume(true);

             }
        }
    }

}
