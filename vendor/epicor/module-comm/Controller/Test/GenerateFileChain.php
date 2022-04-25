<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Test;

class GenerateFileChain extends \Epicor\Comm\Controller\Test
{



    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;


    /**
     * @var \Epicor\Common\Model\FileFactory
     */
    protected $commonFileFactory;



    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        \Magento\Framework\Module\Dir\Reader $moduleReader,
        \Epicor\Comm\Helper\Data $commHelper,
        \Epicor\Common\Model\FileFactory $commonFileFactory,
        \Magento\Framework\App\CacheInterface $cacheManager)
    {
        $this->commHelper = $commHelper;
        $this->commonFileFactory = $commonFileFactory;
        parent::__construct(
            $context,
            $resourceConfig,
            $moduleReader,
            $cacheManager);
    }

    public function execute()
    {

        $helper = $this->commHelper;
        /* @var $helper Epicor_Common_Helper_File */

        $file = $this->commonFileFactory->create()->load($this->getRequest()->getParam('id'));

        $data = array(
            'web_file_id' => $file->getId(),
            'erp_file_id' => $file->getErpId()
        );

        $chain = base64_encode($helper->urlEncode(serialize($data)));

        echo $chain;
    }

    }
