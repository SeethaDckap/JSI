<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Data;

class Responder extends \Epicor\Comm\Controller\Data
{

    /**
     * @var \Epicor\Comm\Helper\Product
     */
    protected $commProductHelper;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $catalogProductFactory;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $generic;

    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Epicor\Comm\Helper\Product $commProductHelper,
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory,
        \Magento\Framework\Session\Generic $generic,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper
    ) {
        $this->commProductHelper = $commProductHelper;
        $this->catalogProductFactory = $catalogProductFactory;
        $this->generic = $generic;
        $this->commMessagingHelper = $commMessagingHelper;
        parent::__construct(
            $context
        );
    }


/**
     * ERP to post data to this action
     */
    public function execute()
    {
        //ini_set('memory_limit', '512M');
        $message_helper = $this->commMessagingHelper;
        $message_helper->setPhpMemoryLimits();
        
        /* @var $message_helper Epicor_Comm_Helper_Messaging */

        $xml = $message_helper->formatXml(trim(file_get_contents('php://input')));

        $response = $message_helper->processSingleMessage($xml, true);

        if ($response->getIsAuthorized()) {
            $httpStatusCode = 200;
        } else {
            $httpStatusCode = 403;
        }
//        ob_clean();
        $this->getResponse()
            ->setHeader('Pragma', 'public', true)
            ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
            ->setHttpResponseCode($httpStatusCode)
            ->setHeader('Content-Length', strlen($response->getXmlResponse()));

        echo $response->getXmlResponse();
    }

    }
