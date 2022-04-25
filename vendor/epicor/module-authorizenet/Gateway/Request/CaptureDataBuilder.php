<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Authorizenet\Gateway\Request;

use Magento\AuthorizenetAcceptjs\Gateway\SubjectReader;
use Magento\AuthorizenetAcceptjs\Model\PassthroughDataObject;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\Payment\Helper\Formatter;

class CaptureDataBuilder implements BuilderInterface
{
    use Formatter;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var PassthroughDataObject
     */
    private $passthroughData;

    private const REQUEST_TYPE_PRIOR_AUTH_CAPTURE = 'priorAuthCaptureTransaction';

    /**
     * @param SubjectReader $subjectReader
     * @param PassthroughDataObject $passthroughData
     */
    public function __construct(
        SubjectReader $subjectReader,
        PassthroughDataObject $passthroughData
    ) {
        $this->subjectReader = $subjectReader;
        $this->passthroughData = $passthroughData;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject): array
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $payment = $paymentDO->getPayment();
        $data = [];

        if ($payment instanceof Payment) {
            $authTransaction = $payment->getAuthorizationTransaction();
            $refId = $authTransaction->getAdditionalInformation('real_transaction_id');

            if (array_key_exists('amount', $buildSubject)) {
                $data = [
                    'transactionRequest' => [
                        'transactionType' => self::REQUEST_TYPE_PRIOR_AUTH_CAPTURE,
                        'amount' => $this->formatPrice($this->subjectReader->readAmount($buildSubject)),
                        'refTransId' => $refId
                    ]
                ];
            } else {
                $data = [
                    'transactionRequest' => [
                        'transactionType' => self::REQUEST_TYPE_PRIOR_AUTH_CAPTURE,
                        'refTransId' => $refId
                    ]
                ];
            }

            $this->passthroughData->setData(
                'transactionType',
                $data['transactionRequest']['transactionType']
            );
        }

        return $data;
    }
}