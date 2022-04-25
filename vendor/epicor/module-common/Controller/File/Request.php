<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Common\Controller\File;

use \Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Controller\ResultFactory;
class Request extends \Epicor\Common\Controller\File {

    /**
     * @var \Epicor\Common\Helper\File
     */
    protected $commonFileHelper;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Framework\Url\DecoderInterface
     */
    protected $urlDecoder;

    /**
     * 
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Epicor\Common\Helper\File $commonFileHelper
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\Url\DecoderInterface $urlDecoder
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context, 
        \Epicor\Common\Helper\File $commonFileHelper, 
        \Magento\Framework\App\Request\Http $request,        
        \Magento\Framework\Url\DecoderInterface $urlDecoder
    ) {
        $this->commonFileHelper = $commonFileHelper;
        $this->request = $request;
        $this->urlDecoder = $urlDecoder;
        parent::__construct($context);
    }

    /**
     * File Request Action
     */
    public function execute() {
        $helper = $this->commonFileHelper;
        /* @var $helper \Epicor\Common\Helper\Data */
        $file = unserialize($this->urlDecoder->decode(base64_decode($this->request->getParam('file'))));
        try {
            $helper->serveFile($file);
        } catch (NotFoundException $ex) {
            //Forward to 404 Error page 
            $this->messageManager->addErrorMessage($ex->getMessage());
            
            return $this->resultFactory->create(ResultFactory::TYPE_FORWARD)->forward('noroute');
        }
    }
}
