<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Model;


/**
 * 
 * File model
 * 
 * @category   Epicor
 * @package    Epicor_Common
 * @author     Epicor Websales Team
 * 
 * @method string getErpId()
 * @method string getFilename()
 * @method string getDescription()
 * @method string getUrl()
 * @method string getSource()
 * @method string getAction()
 * @method string getPreviousData()
 * @method integer getCustomerId()
 * @method integer getErpAccountId()
 * @method datetime getCreatedAt()
 * @method datetime getUpdatedAt()
 * @method setErpId(string $erpId)
 * @method setFilename(string $filename)
 * @method setDescription(string $description)
 * @method setUrl(string $url)
 * @method setSource(string $source)
 * @method setAction(string $action)
 * @method setPreviousData(string $data)
 * @method setCustomerId(integer $customerId)
 * @method setErpAccountId(integer $erpAccountId)
 * @method setCreatedAt(datetime $date)
 * @method setUpdatedAt(datetime $date)
 */
class File extends \Epicor\Database\Model\File
{

    /**
     * @var \Epicor\Common\Helper\File
     */
    protected $commonFileHelper;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Epicor\Common\Helper\File $commonFileHelper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->commonFileHelper = $commonFileHelper;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }


    public function _construct()
    {
        $this->_init('Epicor\Common\Model\ResourceModel\File');
    }

    /**
     * Returns an array of files with names
     * 
     * @return array
     */
    public function toOptionArray()
    {
        $arr = array();
        $collection = $this->getCollection();
        foreach ($collection->getItems() as $group) {
            $arr[] = array('label' => $group->getFilename(), 'value' => $group->getEntityId());
        }
        return $arr;
    }

    public function restorePreviousData()
    {
        $data = unserialize($this->getPreviousData());

        $this->setFilename($data['filename']);
        $this->setDescription($data['description']);
        $this->setUrl($data['url']);
        $this->setUpdatedAt(@$data['update_at']);
        $this->setAction($data['action']);
        $this->setPreviousData($data['previous_data']);
    }

    protected function _afterDelete()
    {
        $helper = $this->commonFileHelper;
        /* @var $helper Epicor_Common_Helper_File */
        $helper->removeFile($this);
        parent::_afterDelete();
    }

    public function makeFileKey()
    {
        return base64_encode($this->getId() . 'ECCfile1234!');
    }

    public function beforeSave()
    {
        if ($this->isObjectNew()) {
            //M1 > M2 Translation Begin (Rule 25)
            //$this->setCreatedAt(now());
            $this->setCreatedAt(date('Y-m-d H:i:s'));
            //M1 > M2 Translation End
        }

        //M1 > M2 Translation Begin (Rule 25)
        //$this->setUpdatedAt(now());
        $this->setUpdatedAt(date('Y-m-d H:i:s'));
        //M1 > M2 Translation End
        parent::beforeSave();
    }

    /**
     * Duplicates the file
     * 
     * @return array     
     */
    public function duplicate($getRemote = false)
    {
        $helper = $this->commonFileHelper;
        /* @var $helper Epicor_Common_Helper_File */

        $content = $helper->getFileContent($this, \Epicor\Common\Helper\File::DATATYPE_DATA);

        if ($content == false && $getRemote && $this->getErpId()) {
            $content = $helper->getRemoteContent($this->getId(), $this->getErpId(), $this->getFilename(), $this->getUrl());
        }

        $fileData = $helper->processFileUpload(array(
            'name' => $this->getFilename(),
            'content' => $content,
            'description' => $this->getDescription(),
            'erp_file_id' => '',
            'web_file_id' => '',
            'url' => '',
            'source' => 'web',
            'customer_id' => '',
            'erp_account_id' => ''
        ));

        unset($fileData['content']);

        return $fileData;
    }

}
