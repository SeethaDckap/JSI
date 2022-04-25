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

/**
 * Mapping resolver reader interface
 */
interface ConfigReaderInterface
{
    /**
     * Get data.
     *
     * @param string $path Config path.
     *
     * @return string
     */
    public function getData($path);


}
