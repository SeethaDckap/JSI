<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
//M1 > M2 Translation Begin (Rule 46)

namespace Epicor\Common\Model;


class MessageUploadModelReader
{
    protected $readers;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;


    public function __construct(
        $readers,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->readers = $readers;
    }

    /**
     * @param $messageBase
     * @param $messageType
     * @return \Epicor\Comm\Model\Message\Request
     */
    public function getModel($messageBase, $messageType)
    {
        $messageType = strtolower($messageType);
        $newUpload = $this->scopeConfig->isSetFlag('epicor_comm_field_mapping/stk_mapping/new_upload', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if( in_array($messageType,['stk']) && $newUpload){
            $messageType='stknew';
        }
        return $this->readers[$messageBase. '_'. $messageType];
    }
}

//M1 > M2 Translation End
