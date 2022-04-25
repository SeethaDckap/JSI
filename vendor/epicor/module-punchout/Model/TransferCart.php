<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Punchout
 * @subpackage Helper
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Punchout\Model;


use Epicor\Comm\Model\Serialize\Serializer\Json as Serializer;
use Epicor\Punchout\Api\ConnectionsRepositoryInterface;
use Epicor\Punchout\Helper\Data;

/**
 * TransferCart Class.
 */
class TransferCart extends AbstractPunchout
{

    /**
     * Attribute key word
     *
     * @var string
     */
    protected $_attributes = '_attributes';

    /**
     * Connection attribute mapping
     *
     * @var array
     */
    protected $mapping = [];

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $localeResolver;

    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    protected $countryFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * Json serializer.
     *
     * @var Serializer
     */
    protected $serializer;

    /**
     * Helper.
     *
     * @var helper
     */
    protected $helper;

    /**
     * @var TransferCartItem
     */
    protected $proccessQuoteItem;


    /**
     * Construction function.
     *
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param Data $helper Helper class.
     * @param TransferCartItem $proccessQuoteItem
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Customer\Model\Session $customerSession,
        Data $helper,
        TransferCartItem $proccessQuoteItem
    )
    {
        $this->storeManager = $storeManager;
        $this->checkoutSession = $checkoutSession;
        $this->proccessQuoteItem = $proccessQuoteItem;
        $this->localeResolver = $localeResolver;
        $this->countryFactory = $countryFactory;
        $this->customerSession = $customerSession;
        $this->serializer = $helper->getSerializer();
        $this->helper = $helper;

    }//end __construct()


    /**
     * @return \Magento\Directory\Model\CountryFactory
     */
    public function getDirectoryCountryFactory()
    {
        return $this->countryFactory->create();
    }


    /**
     * logout Punchout session.
     *
     * @return mixed
     */
    public function logoutPunchoutSession()
    {
        $quote = $this->checkoutSession->getQuote();
        $quote->setIsActive(0)->save();
        $this->customerSession->logout();
    }

    /**
     * Return punchout Order Xml.
     *
     * @param ConnectionsRepositoryInterface $connection
     * @return array
     */
    public function getPunchoutOrderXml($connection, $cancel = false)
    {

        $mappingarray = $this->serializer->unserialize($connection->getMappings());
        $this->mapping = $this->proccessQuoteItem->processMapping($mappingarray);
        $finalarray = [];
        $fromDatetimestamp = strtotime(time());
        $fromDate = $this->helper->getCommHelper()->UTCwithOffset($fromDatetimestamp);
        $header = $this->getHeaderInfo($connection);
        $currentLocaleCode = $this->localeResolver->getLocale();
        $finalarray['cXML'] = [
            $this->_attributes => [
                'xml:lang' => $currentLocaleCode,
                'payloadID' => $this->getPayloadID(),
                'timestamp' => $fromDate
            ]
        ];
        $finalarray['cXML']['Header'] = $header;
        $finalarray['cXML']['Message'] = [
            $this->_attributes => [
                'deploymentMode' => 'production'
            ],
            'PunchOutOrderMessage' => $this->getPunchOutOrderMessage($cancel)
        ];

        return $finalarray;
    }

    /**
     * Prepare Header Tag.
     *
     * @return array
     */
    private function getHeaderInfo($connection)
    {
        $header = [];
        $header['From'] = [
            'Credential' => [
                $this->_attributes => [
                    'domain' => 'NetworkID'
                ],
                'Identity' => 'Epicor'
            ]
        ];
        $header['To'] = [
            'Credential' => [
                $this->_attributes => [
                    'domain' => $connection->getDomain()
                ],
                'Identity' => $connection->getIdentity()
            ]
        ];
        $header['Sender'] = [
            'Credential' => [
                $this->_attributes => [
                    'domain' => 'NetworkID'
                ],
                'Identity' => 'Epicor',
                'SharedSecret' => ''
            ],
            'UserAgent' => ''
        ];
        return $header;

    }

    /**
     * Prepare PunchOut Order Message.
     *
     * @return array
     */
    private function getPunchOutOrderMessage($cancel = false)
    {
        $pOMessage = [
            'BuyerCookie' => $this->customerSession->getBuyerCookie(),
        ];
        if (!$cancel) {
            $currencyCode = $this->storeManager->getStore()->getBaseCurrencyCode();
            $quote = $this->checkoutSession->getQuote();
            $pOMessage['PunchOutOrderMessageHeader'] = [
                $this->_attributes => [
                    'operationAllowed' => 'edit'
                ],
                'Total' => [
                    'Money' => [
                        $this->_attributes => [
                            'currency' => $currencyCode
                        ],
                        $quote->getShippingAddress()->getSubtotalInclTax() + $quote->getShippingAddress()->getBaseDiscountAmount()
                    ]
                ],
                'shipping' => '',
                'tax' => $quote->getShippingAddress()->getTaxAmount(),
                'ShipTo' => $this->getShipTo($quote->getShippingAddress())
            ];
            $pOMessage['ItemIn'] = $this->proccessQuoteItem->getQuoteItems();
        }
        return $pOMessage;
    }

    /**
     *  Order Address tags.
     *
     * @return array
     */
    private function getShipTo($shippingaddress)
    {
        $currentLocaleCode = $this->localeResolver->getLocale();
        $countryname = $this->getDirectoryCountryFactory()
            ->load($shippingaddress->getCountryId())->getName();
        return [
            'Address' => [
                $this->_attributes => [
                    'addressID' => $shippingaddress->getEccErpAddressCode()
                ],
                'Name' => [
                    $this->_attributes => [
                        'xml:lang' => $currentLocaleCode
                    ],
                    $shippingaddress->getName()
                ],
                'PostalAddress' => [
                    'Street' => $shippingaddress->getStreet(),
                    'City' => $shippingaddress->getCity(),
                    'State' => $shippingaddress->getRegion(),
                    'PostalCode' => $shippingaddress->getPostcode(),
                    'Country' => [
                        $this->_attributes => [
                            'isoCountryCode' => $shippingaddress->getCountryId()
                        ],
                        $countryname
                    ]
                ]

            ]
        ];

    }
}//end class