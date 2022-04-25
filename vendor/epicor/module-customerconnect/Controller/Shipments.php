<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Controller;


/**
 * Shipments controller
 *
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
abstract class Shipments extends \Epicor\Customerconnect\Controller\Generic
{

    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $customerconnectHelper;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Epicor\Customerconnect\Model\Message\Request\Cusd
     */
    protected $customerconnectMessageRequestCusd;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $generic;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var \Magento\Framework\Url\DecoderInterface
     */
    protected $urlDecoder;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Magento\Framework\App\Request\Http $request,
        \Epicor\Customerconnect\Model\Message\Request\Cusd $customerconnectMessageRequestCusd,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Session\Generic $generic,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\Url\DecoderInterface $urlDecoder
    )
    {
        $this->customerconnectHelper = $customerconnectHelper;
        $this->request = $request;
        $this->customerconnectMessageRequestCusd = $customerconnectMessageRequestCusd;
        $this->registry = $registry;
        $this->generic = $generic;
        $this->encryptor = $encryptor;
        $this->urlDecoder = $urlDecoder;
        parent::__construct(
            $context,
            $customerSession,
            $localeResolver,
            $resultPageFactory,
            $resultLayoutFactory
        );
    }

    /**
     * Loads a shipment from the params
     *
     * return boolean
     */
    protected function _loadShipment()
    {
        $loaded = false;
        $helper = $this->customerconnectHelper;
        $erpAccountNumber = $helper->getErpAccountNumber();
        $param = $this->request->getParam('shipment');
        $shipment = explode(']:[', $this->encryptor->decrypt($this->urlDecoder->decode($param)));
        if (
            count($shipment) == 3 &&
            $shipment[0] == $erpAccountNumber &&
            !empty($shipment[1]) &&
            !empty($shipment[2])
        ) {
            $shipment[2] = ($shipment[2] == 'ordernumberempty') ? null : $shipment[2];
            $cusd = $this->customerconnectMessageRequestCusd;
            $messageTypeCheck = $cusd->getHelper()->getMessageType('CUSD');

            if ($cusd->isActive() && $messageTypeCheck) {

                //M1 > M2 Translation Begin (Rule p2-6.4)
                /*$cusd->setAccountNumber($erpAccountNumber)
                    ->setOrderNumber($shipment[2])
                    ->setShipmentNumber($shipment[1])
                    ->setLanguageCode($helper->getLanguageMapping(Mage::app()->getLocale()->getLocaleCode()));*/
                $cusd->setAccountNumber($erpAccountNumber)
                    ->setOrderNumber($shipment[2])
                    ->setShipmentNumber($shipment[1])
                    ->setLanguageCode($helper->getLanguageMapping($this->_localeResolver->getLocale()));
                //M1 > M2 Translation End
                if ($cusd->sendMessage()) {
                    $this->registry->register('customer_connect_shipments_details', $cusd->getResults());
                    $loaded = true;
                } else {
                    $this->messageManager->addErrorMessage("Failed to retrieve Shipment Details");
                }
            } else {
                $this->messageManager->addErrorMessage("ERROR - Shipment Details not available");
            }
        } else {
            $this->messageManager->addErrorMessage("ERROR - Invalid Shipment Number");
        }

        return $loaded;
    }

}
