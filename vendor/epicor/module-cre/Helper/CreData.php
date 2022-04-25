<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Cre\Helper;

class CreData extends \Magento\Framework\App\Helper\AbstractHelper
{

    const TEST_MODE = 0;
    const LIVE_MODE = 1;

    private $_encryptor;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    protected $_storeManager;

    protected $customerSession;

    protected $tokenCollectionFactory;

    protected $assetRepo;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;


    protected $messageManager;

    /**
     * Constant for CRE and ECC card type abbreviations mapping.
     * array key: CRE card type abbreviation
     * array value: ECC card type abbreviation
     * AE -> American Express
     * VI -> Visa
     * MC -> MasterCard
     * DI -> Discover
     * JCB -> JCB
     * DN -> Diners
     * UN -> Unionpay
     */
    const ECC_CRE_CARD_TYPE_MAP = array(
        'AX' => 'AE',
        'VI' => 'VI',
        'MC' => 'MC',
        'DS' => 'DI',
        'JC' => 'JCB',
        'DC' => 'DN',
        'UP' => 'UP'
    );

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Epicor\Cre\Logger\Logger $logger,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->_encryptor = $encryptor;
        $this->_storeManager = $storeManager;
        $this->logger          = $logger;
        $this->messageManager  = $messageManager;
        $this->customerSession = $customerSession;
        $this->assetRepo  = $assetRepo;
    }


    public function getLive()
    {
        return $this->scopeConfig->getValue('payment/cre/live_mode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) == self::LIVE_MODE;
    }

    public function allowedCardTypeLogos()
    {
        $cardTypeImages = [];
        $acceptingCardTypes = explode(',', $this->getConfigValue('cctypes'));
        if (in_array('MC', $acceptingCardTypes)) $acceptingCardTypes[] = 'MI';
        foreach ($acceptingCardTypes as $cardType) {
            $cardTypeImages[] = $this->getCardTypeImage($cardType);
        }
        return $cardTypeImages;
    }

    public function generateCreRequestData()
    {

        $controllerUrl = $this->_storeManager->getStore()->getUrl('cre/cards/opcsavereview');
        $data = [
            'urlAjax' => $controllerUrl,
            'configurations' => $this->getCreHostedConfigurations(),
            'allowedCardsLogo' => $this->allowedCardTypeLogos()

        ];
        $this->logger->info(\Psr\Log\LogLevel::DEBUG, $data);
        return $data;
    }

    public function getConfigValue($key)
    {
        return $this->scopeConfig->getValue('payment/cre/' . $key, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }


    public function getCreHostedConfigurations()
    {
        if ($this->getLive()) {
            $url = $this->getConfigValue('live_url');
        } else {
            $url = $this->getConfigValue('test_url');
        }
        if($url){
            $script['error'] = '';
            $parseUrl        = parse_url($url);
            $fullUrl         = '';
            $error           = false;
            try {
                $scheme  = $parseUrl['scheme'];
                $host    = $parseUrl['host'];
                $fullUrl = $scheme . "://" . $host;
            }
            catch (\Exception $e) {
                $this->logger->log(\Psr\Log\LogLevel::DEBUG, $e);
                $error = $e->getMessage();
            }
            $script = array(
                'public_key' => $this->getConfigValue('public_key'),
                'namespace' => $this->getConfigValue('namespace'),
                'body_background' => $this->getConfigValue('body_background'),
                'font_color' => $this->getConfigValue('font_color'),
                'font_background' => $this->getConfigValue('font_background'),
                'payment_title' => $this->getConfigValue('payment_title'),
                'cctypes' => $this->getConfigValue('cctypes'),
                'button_name' => $this->getConfigValue('button_name'),
                'payment_url' => $url,
                'short_url' => $fullUrl,
                'error' => $error,
                'ecc_cre_card_type_map' => json_encode(array_flip(self::ECC_CRE_CARD_TYPE_MAP))
            );
            return $script;
        }

    }

    public function getCreLogo()
    {
        $createAsset = $this->assetRepo->createAsset('Epicor_Cre::images/esdm.jpg');
        return $createAsset->getUrl();
    }


    public function getCardTypeImage($cardType)
    {
        $stringToLower = strtolower($cardType);
        $createAsset = $this->assetRepo->createAsset('Epicor_Cre::images/cardtypes/'.$stringToLower.'.gif');
        return $createAsset->getUrl();
    }


}
