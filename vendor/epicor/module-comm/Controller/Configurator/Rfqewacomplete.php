<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Configurator;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\View\Result\LayoutFactory;

/**
 * Class Ewacomplete
 * @package Epicor\Comm\Controller\Configurator
 */
class Rfqewacomplete extends Action
{
    /**
     * @var Http
     */
    private $request;

    /**
     * @var FormKey
     */
    private $formKey;

    /**
     * @var LayoutFactory
     */
    private $resultLayoutFactory;


    /**
     * Ewacomplete constructor.
     * @param FormKey $formKey
     * @param Http $request
     * @param LayoutFactory $resultLayoutFactory
     * @param Context $context
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        FormKey $formKey,
        Http $request,
        LayoutFactory $resultLayoutFactory,
        Context $context
    ) {
        $this->request = $request;
        $this->formKey = $formKey;
        $this->request->setParam('form_key', $this->formKey->getFormKey());
        $this->resultLayoutFactory = $resultLayoutFactory;
        parent::__construct(
            $context
        );
    }

    /**
     * Redirection from EWA to ECC
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Layout
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $resultLayout = $this->resultLayoutFactory->create();
        $resultLayout->addDefaultHandle();
        $resultLayout->getLayout()->getUpdate()->load(['epicor_comm_configurator_rfqewacomplete']);
        return $resultLayout;
    }

}