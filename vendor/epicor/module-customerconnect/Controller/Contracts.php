<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Controller;


/**
 * Invoices controller
 *
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
abstract class Contracts extends \Epicor\Customerconnect\Controller\Generic
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
     * @var \Epicor\Customerconnect\Model\Message\Request\Cuid
     */
    protected $customerconnectMessageRequestCccs;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $generic;

    /**
     * @var \Magento\Framework\Url\DecoderInterface
     */
    protected $urlDecoder;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptor;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Magento\Framework\App\Request\Http $request,
        \Epicor\Customerconnect\Model\Message\Request\Cccs $customerconnectMessageRequestCccs,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Session\Generic $generic,
        \Magento\Framework\Url\DecoderInterface $urlDecoder,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor
    )
    {
        $this->customerconnectHelper = $customerconnectHelper;
        $this->request = $request;
        $this->customerconnectMessageRequestCccs = $customerconnectMessageRequestCccs;
        $this->registry = $registry;
        $this->generic = $generic;
        $this->urlDecoder = $urlDecoder;
        $this->encryptor = $encryptor;
        parent::__construct(
            $context,
            $customerSession,
            $localeResolver,
            $resultPageFactory,
            $resultLayoutFactory
        );
    }
}
