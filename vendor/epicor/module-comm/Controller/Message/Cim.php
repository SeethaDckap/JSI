<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Message;

class Cim extends \Epicor\Comm\Controller\Message
{

    /**
     * @var \Epicor\Comm\Helper\Configurator
     */
    protected $commConfiguratorHelper;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Epicor\Comm\Helper\Configurator $commConfiguratorHelper,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->commConfiguratorHelper = $commConfiguratorHelper;
        $this->request = $request;
        parent::__construct(
            $context
        );
    }


public function execute()
    {
        $helper = $this->commConfiguratorHelper;
        /* @var $helper Epicor_Comm_Helper_Configurator */
        $ewaCode = '';
        try {

            $productId = $this->request->getParam('productId');
            $quoteId = $this->request->getParam('quoteId');
            $action = $this->request->getParam('action');
            $lineNum = $this->request->getParam('lineNumber');

            $cimData = array(
                'ewa_code' => $this->request->getParam('ewaCode'),
                'group_sequence' => $this->request->getParam('groupSequence'),
                'quote_id' => !empty($quoteId) ? $quoteId : null,
                'action' => $action,
                'line_number' => $lineNum
            );

            $cim = $helper->sendCim($productId, $cimData);

            if ($cim->isSuccessfulStatusCode()) {
                $configurator = $cim->getResponse()->getConfigurator();
                $ewaCode = $configurator->getRelatedToRowId();
            } else {
                $error = __('Failed to retrieve configured details.');
            }
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }

        $ewaAttributes = array(
            array('description' => 'Ewa Code', 'value' => $ewaCode),
        );

        $response = array(
            'error' => isset($error) ? $error : '',
            'ewa_code' => $ewaCode,
            'ewa_attributes' => base64_encode(serialize($ewaAttributes))
        );

        $this->getResponse()->setBody(json_encode($response));
    }

    }
