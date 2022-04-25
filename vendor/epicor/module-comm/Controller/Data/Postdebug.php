<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Data;

class Postdebug extends \Epicor\Comm\Controller\Data
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
     * @var \Epicor\Common\Helper\Xml
     */
    protected $commonXmlHelper;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Epicor\Comm\Helper\Product $commProductHelper,
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory,
        \Magento\Framework\Session\Generic $generic,
        \Epicor\Common\Helper\Xml $commonXmlHelper
    ) {
        $this->commProductHelper = $commProductHelper;
        $this->catalogProductFactory = $catalogProductFactory;
        $this->generic = $generic;
        $this->commonXmlHelper = $commonXmlHelper;
        parent::__construct(
            $context
        );
    }


public function execute()
    {
        echo '<pre>';
        $xml = trim(file_get_contents('php://input'));

        $helper = $this->commonXmlHelper;
        /* @var $helper Epicor_Common_Helper_Xml */
        print_r(getallheaders());
        #print_r(());
        print_r($_POST);
        print_r($_GET);
        echo $xml;
        exit;
    }

    }
