<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Message\Request;


/**
 * Request HRT - Heart Beat
 *
 * Get the account information for the specified customer account
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 *
 */
class Hrt extends \Epicor\Comm\Model\Message\Request
{

    /**
     *
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $_helper;

    /**
     * Construct object and set message type.
     */
    public function __construct(
        \Epicor\Comm\Model\Context $context,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [])
    {
        parent::__construct($context, $resource, $resourceCollection, $data);
        $this->setMessageType('HRT');
        $this->setLicenseType(array('Consumer', 'Customer', 'Supplier'));
        $this->setConfigBase('epicor_comm_enabled_messages/hrt_request/');
        $this->_helper = $this->commMessagingHelper;

    }
    public function buildRequest()
    {
        $message = $this->getMessageTemplate();
        $message['messages']['request']['body'] = array_merge($message['messages']['request']['body'], array(
            'serviceAvailable' => $this->getServerAvailability(),
        ));

        $this->setOutXml($message);
        return true;
    }

    public function processResponse()
    {
        $online = false;

        if ($this->isSuccessfulStatusCode())
            $online = true;

        //M1 > M2 Translation Begin (Rule P2-5.6)
        //$config = Mage::getConfig();

        //M1 > M2 Translation End

        if ($online) {
            $this->configConfig
                ->setSection('Epicor_Comm')
                ->setWebsite(null)
                ->setStore(null)
                ->setGroups(array(
                    'xmlMessaging' => array(
                        'fields' => array(
                            'failed_msg_count' => array(
                                'value' => 0
                            )
                        )
                    )
                ))
                ->save();
        }

        $this->configConfig
            ->setSection('Epicor_Comm')
            ->setWebsite(null)
            ->setStore(null)
            ->setGroups(array(
                'xmlMessaging' => array(
                    'fields' => array(
                        'failed_msg_online' => array(
                            'value' => $online
                        )
                    )
                )
            ))
            ->save();
        //M1 > M2 Translation Begin (Rule P2-5.6)
        //$config->reinit();

        //M1 > M2 Translation End

        //M1 > M2 Translation Begin (Rule p2-6.11)
        //Mage::app()->reinitStores();
        $this->storeManager->reinitStores();
        //M1 > M2 Translation End

        return $online;
    }

    protected function getServerAvailability()
    {
        return $this->scopeConfig->isSetFlag('Epicor_Comm/xmlMessaging/disable_comms', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ? 'N' : 'Y';
    }

}
