<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\ReleaseNotification\Service;

use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Xml\Parser;

class Configuration
{
    /**
     * @var Reader
     */
    private $moduleDirReader;

    /**
     * @var Parser
     */
    private $parser;

    /**
     * Configuration constructor.
     *
     * @param Reader $moduleDirReader
     * @param Parser $parser
     */
    public function __construct(
        Reader $moduleDirReader,
        Parser $parser
    ) {
        $this->moduleDirReader = $moduleDirReader;
        $this->parser = $parser;
    }

    /**
     * Finds the ECC version from the current module.
     *
     * @return string
     */
    public function getEccVersion()
    {
        $filePath = $this->moduleDirReader->getModuleDir('etc', 'Epicor_ReleaseNotification')
            . '/global.xml';
        $parsedArray = $this->parser->load($filePath)->xmlToArray();

        return $parsedArray['config']['global']['ecc_version_info']['Epicor_ReleaseNotification']['version'];
    }

    /**
     * Is this a released version?
     *
     * @return bool
     */
    public function isReleased()
    {
        $filePath = $this->moduleDirReader->getModuleDir('etc', 'Epicor_ReleaseNotification')
            . '/global.xml';
        $parsedArray = $this->parser->load($filePath)->xmlToArray();

        return (bool)$parsedArray['config']['global']['ecc_version_info']['Epicor_ReleaseNotification']['released'];
    }
}
