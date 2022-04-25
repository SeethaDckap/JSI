<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
//M1 > M2 Translation Begin (Rule 4)
namespace Epicor\Comm\Model\GlobalConfig\Config;


class Reader extends \Magento\Framework\Config\Reader\Filesystem
{
    public function __construct(
        \Magento\Framework\Config\FileResolverInterface $fileResolver,
        \Epicor\Comm\Model\GlobalConfig\Config\Converter $converter,
        \Epicor\Comm\Model\GlobalConfig\Config\SchemaLocator $schemaLocator,
        \Magento\Framework\Config\ValidationStateInterface $validationState,
        $fileName = 'global.xml',
        array $idAttributes = [],
        $domDocumentClass = 'Epicor\Comm\Model\GlobalConfig\Config\Dom',
        $defaultScope = 'global'
    )
    {
        parent::__construct($fileResolver, $converter, $schemaLocator, $validationState, $fileName, $idAttributes, $domDocumentClass, $defaultScope);
    }
}
//M1 > M2 Translation End