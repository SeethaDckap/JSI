<?php 

namespace Silk\CustomForms\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_parser;
    protected $_curl;
    protected $_logger;
    protected $_transportBuilder;
    protected $_storeManager;
    protected $_inlineTranslation;
    protected $_scopeConfig;
    
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_logger = $logger;
        $this->_transportBuilder = $transportBuilder;
        $this->_storeManager = $storeManager;
        $this->_scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    public function sendEmail($message, $receivers, $templateId)
    {
        $sender = 'tmp@test.com';
        // $sender = $this->_scopeConfig->getValue('catalog/silk_import/notificationSender', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        // $receivers = $this->_scopeConfig->getValue('catalog/silk_import/notificationReceivers', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $receivers = array_map("trim", explode(",", $receivers));

        $templateId = $templateId;
        $fromEmail = $sender;
        $fromName = 'Admin';
 
        try {
            $templateVars = [
                'message' => $message,
            ];
 
            $storeId = $this->_storeManager->getStore()->getId();
 
            $from = ['email' => $fromEmail, 'name' => $fromName];
 
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            foreach($receivers as $receiver){
                if($receiver == ""){
                    continue;
                }
                $transport = $this->_transportBuilder
                    ->setTemplateIdentifier($templateId, $storeScope)
                    ->setTemplateOptions(['area' => 'frontend', 'store' => 1])
                    ->setTemplateVars($templateVars)
                    ->setFrom($from)
                    ->addTo($receiver)
                    ->getTransport();
                    $transport->sendMessage();
            }
        } catch (\Exception $e) {
            $this->_logger->info($e->getMessage());
        }
    }
}
?>
