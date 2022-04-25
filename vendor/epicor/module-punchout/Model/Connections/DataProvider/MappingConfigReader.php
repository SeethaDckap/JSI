<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Punchout
 * @subpackage Model
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Punchout\Model\Connections\DataProvider;

use Epicor\Comm\Model\GlobalConfig\Config;

/**
 * Class MappingConfigReader
 */
class MappingConfigReader implements ConfigReaderInterface
{

    /**
     * Global config.
     *
     * @var Config
     */
    private $globalConfig;


    /**
     * CategoryItemResolverConfigReader constructor.
     *
     * @param Config $globalConfig Global config reader.
     */
    public function __construct(Config $globalConfig)
    {
        $this->globalConfig = $globalConfig;
    }


    /**
     * Get data.
     *
     * @param string $path Config path.
     *
     * @return string
     */
    public function getData($path)
    {
       return $this->globalConfig->get($path);

    }//end getData()


}//end class
