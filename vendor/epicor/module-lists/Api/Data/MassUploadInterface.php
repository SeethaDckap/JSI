<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Api\Data;

/**
 * CAAP DATA async Consumer Queue basic interface.
 */
interface MassUploadInterface
{
    /**
     * Id.
     *
     * @return mixed
     */
    public function getId();

    /**
     * Set Id.
     *
     * @param string $id Id.
     *
     * @return mixed
     */
    public function setId($id);

    /**
     * Returns Csv File Name.
     *
     * @return mixed
     */
    public function getFileName();

    /**
     * Set Csv File Name.
     *
     * @param string $fileName FileName.
     *
     * @return mixed
     */
    public function setFileName($fileName);
}
