<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Lists
 * @subpackage Api
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Lists\Api;

use Epicor\Lists\Api\Data\ImportInterface;

/**
 * Interface ImportRepositoryInterface
 *
 * @package Epicor\Lists\Api
 */
interface ImportRepositoryInterface
{
    /**
     * save Import.
     *
     * @param ImportInterface $import
     *
     * @return mixed
     */
    public function save(ImportInterface $import);


    /**
     * @param string $id
     *
     * @return mixed
     */
    public function getById($id);

    /**
     * @param null $id
     *
     * @return mixed
     */
    public function loadEntity($id = null);

    /**
     * @param ImportInterface $import
     *
     * @return mixed
     */
    public function delete(ImportInterface $import);


}
