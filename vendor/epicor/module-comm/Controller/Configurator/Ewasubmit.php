<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Configurator;

use Epicor\Comm\Helper\Configurator;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;

/**
 * Class Ewasubmit
 * @package Epicor\Comm\Controller\Configurator
 */
class Ewasubmit extends \Epicor\Comm\Controller\Configurator
{

    /**
     * @var Configurator
     */
    private $commConfiguratorHelper;

    /**
     * @var Http
     */
    private $request;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
    */
    private $cookieManager;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var FormKey
     */
    private $formKey;

    /**
     * Ewasubmit constructor.
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param Context $context
     * @param Configurator $commConfiguratorHelper
     * @param FormKey $formKey
     * @param Http $request
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        Context $context,
        Configurator $commConfiguratorHelper,
        FormKey $formKey,
        Http $request
    ) {
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->commConfiguratorHelper = $commConfiguratorHelper;
        $this->request = $request;
        $this->formKey = $formKey;
        $this->request->setParam('form_key', $this->formKey->getFormKey());
        parent::__construct(
            $context
        );
    }

    /**
     * ECC submitting the form from Ewacomplete due to samesite cookie
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException
     * @throws \Magento\Framework\Stdlib\Cookie\FailureToSendException
     */
    public function execute()
    {
        $helper = $this->commConfiguratorHelper;
        /* @var $helper Epicor_Comm_Helper_Configurator */

        $ewaCode = $helper->getUrlDecoder()->decode($this->request->getParam('EWACode'));
        $productSku = $helper->getUrlDecoder()->decode($this->request->getParam('SKU'));
        $locationCode = $helper->getUrlDecoder()->decode($this->request->getParam('location'));
        $itemId = $helper->getUrlDecoder()->decode($this->request->getParam('itemId'));
        $qty = $helper->getUrlDecoder()->decode($this->request->getParam('qty'));
        $qty = $qty ? $qty : 1;
        $url = $helper->addProductToBasket($productSku, $ewaCode, false, $qty, $locationCode, $itemId);

        $url = $url ? '"' . $url . '"' : '"toplocationreload"';

        $metadata = $this->cookieMetadataFactory
            ->createPublicCookieMetadata()
            ->setPath('/');
        $sectiondata = json_decode($this->cookieManager->getCookie('section_data_ids'));
        if(!property_exists($sectiondata, 'cart')) {
            $sectiondata->cart = 0;
        }
        $sectiondata->cart += 1000;
        $this->cookieManager->setPublicCookie(
            'section_data_ids',
            json_encode($sectiondata),
            $metadata
        );
        $this->_response->setBody('<script type="text/javascript">
            //<![CDATA[ 
                    var checkUrl = '.$url.';
                    if(checkUrl ==="toplocationreload") {
                        window.top.location.reload();  
                    } else {
                        window.top.location.href = '.$url.';
                    } 
            //]]>
        </script>');
    }

}