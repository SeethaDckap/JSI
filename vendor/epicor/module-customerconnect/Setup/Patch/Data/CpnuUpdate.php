<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Setup\Patch\Data;

use Epicor\Customerconnect\Model\Skus\CpnuManagement;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class CpnuUpdate
 * @package Epicor\Customerconnect\Setup\Patch\Data
 */
class CpnuUpdate implements DataPatchInterface
{
    /**
     * Configuration path for ERP
     */
    const XML_PATH_ERP = 'Epicor_Comm/licensing/erp';

    /**
     * @var WriterInterface
     */
    private $writer;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * CpnuUpdate constructor.
     * @param WriterInterface $writer
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        WriterInterface $writer,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->writer = $writer;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $value = 1;
        if ($this->scopeConfig->getValue(self::XML_PATH_ERP, ScopeInterface::SCOPE_STORE) == 'p21') {
            $value = 0;
        }

        $this->writer->save(CpnuManagement::XML_PATH_CPNU_ERP_UPDATE, $value);
    }
}
