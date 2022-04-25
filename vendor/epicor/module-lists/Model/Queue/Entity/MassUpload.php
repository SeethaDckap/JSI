<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Lists\Model\Queue\Entity;

use Epicor\Lists\Api\Data\MassUploadInterface;

/**
 * Class Massupload
 *
 * @package Epicor\Lists\Model\Queue
 */
class MassUpload implements MassUploadInterface
{

    /**
     * Id.
     *
     * @var string
     */
    private $id;

    /**
     * File Name.
     *
     * @var string
     */
    private $fileName;

    /**
     * Get Id.
     *
     * @return mixed|string
     */
    public function getId()
    {
        return $this->id;

    }//end getId()


    /**
     * Set Id.
     *
     * @param string $id Id.
     *
     * @return mixed|void
     */
    public function setId($id)
    {
        $this->id = $id;

    }//end setId()

    /**
     * Get Id.
     *
     * @return mixed|string
     */
    public function getFileName()
    {
        return $this->fileName;

    }//end getFileName()


    /**
     * Set File Name.
     *
     * @param string $fileName
     *
     * @return mixed|void
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;

    }//end setFileName()


}
