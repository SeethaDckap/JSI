<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\B2b\Model\Config\Source;


class AccountType
{
    /**
     * @var \Epicor\Comm\Model\GlobalConfig\Config
     */
    protected $globalConfig;

    /**
     * @var \Epicor\Common\Helper\Xml
     */
    protected $commonXmlHelper;

    public function __construct(
        \Epicor\Comm\Model\GlobalConfig\Config $globalConfig,
        \Epicor\Common\Helper\Xml $commonXmlHelper
    )
    {
        $this->globalConfig = $globalConfig;
        $this->commonXmlHelper = $commonXmlHelper;
    }

    public function toOptionArray()
    {
        $types = [];
        $helper = $this->commonXmlHelper;
        $acctTypes = (array)$this->globalConfig->get('allowed_account_types');
        foreach ($acctTypes as $type => $info) {
            if ($helper->isLicensedFor(array($acctTypes[$type]['license']))) {
                array_push($types, array('value' => $acctTypes[$type]['value'], 'label' => $acctTypes[$type]['label'], 'selected' => 'selected'));
            }
        }
        return $types;
    }

}
