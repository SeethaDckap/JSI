<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Controller\MassActions;

use Epicor\Comm\Service\ValidateSkus;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Verifyskus
 * @package Epicor\Customerconnect\Controller\MassActions
 */
class Verifyskus extends Action implements HttpPostActionInterface
{
    /**
     * @var ValidateSkus
     */
    private $validateSkus;

    /**
     * Verifyskus constructor.
     * @param Context $context
     * @param ValidateSkus $validateSkus
     */
    public function __construct(
        Context $context,
        ValidateSkus $validateSkus
    ) {
        parent::__construct($context);
        $this->validateSkus = $validateSkus;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $data = $this->getRequest()->getPost();

        $info = ltrim($data['skus'], ',');
        $skus = explode(',', $info);

        $skus = array_filter($skus, 'strlen');

        $exists = $this->validateSkus->isSkuExist($skus);

        $notExists = array_diff($skus, $exists);

        $noSku = implode(',', $notExists);

        $message = 'no-error';

        if (count($notExists) > 0) {
            if (count($notExists) > 1) {
                $message = 'Skus ' . $noSku . ' not currently available.';
            } else {
                $message = 'Sku ' . $noSku . ' not currently available.';
            }
        }

        echo $message;
    }
}
