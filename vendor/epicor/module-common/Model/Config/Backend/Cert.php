<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Model\Config\Backend;


/**
 * Backend model for saving certificate file in case of using certificate based authentication
 */
class Cert extends \Magento\Framework\App\Config\Value
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Filesystem\Io\FileFactory
     */
    protected $ioFileFactory;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $directoryList;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Filesystem\Io\FileFactory $ioFileFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->ioFileFactory = $ioFileFactory;
        $this->registry = $registry;
        $this->directoryList = $directoryList;
        parent::__construct(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $resource,
            $resourceCollection,
            $data
        );
    }


    /**
     * Process additional data before save config
     *
     * @return \Epicor\Common\Model\Config\Backend\Cert
     */
    public function beforeSave()
    {
        $file = $this->ioFileFactory->create();
        $file->open();

        $value = $this->getValue();
        if (is_array($value) && !empty($value['delete'])) {
            //M1 > M2 Translation Begin (Rule p2-5.5)
            //$file->rm(Mage::getBaseDir() . DS . $value['value']);

            $newFilePath =  $this->directoryList->getPath('pub') . DIRECTORY_SEPARATOR . $value['value'];
            $oldFilePath = $this->directoryList->getRoot() . DIRECTORY_SEPARATOR . $value['value'];

            if(file_exists($newFilePath)){
                $file->rm($newFilePath);
            }
            if(file_exists($oldFilePath)){
                $file->rm($oldFilePath);
            }
            //M1 > M2 Translation End
            $this->setValue('');
        }else {
            if(is_array($value) && isset($value['value'])){
                $this->setValue($value['value']);
            }
        }

        if (!isset($_FILES['groups']['tmp_name'][$this->getGroupId()]['fields'][$this->getField()]['value']) &&
            !isset($_FILES['Epicor_Comm']['tmp_name'][$this->getGroupId()][$this->getField()])) {
            return $this;
        }

        if (isset($_FILES['groups']['tmp_name'][$this->getGroupId()]['fields'][$this->getField()]['value'])) {
            $tmpPath = $_FILES['groups']['tmp_name'][$this->getGroupId()]['fields'][$this->getField()]['value'];
        } else if (isset($_FILES['Epicor_Comm']['tmp_name'][$this->getGroupId()][$this->getField()])) {
            $tmpPath = $_FILES['Epicor_Comm']['tmp_name'][$this->getGroupId()][$this->getField()];
        }

        if ($tmpPath && file_exists($tmpPath)) {
            if (!filesize($tmpPath)) {
                throw new \Magento\Framework\Exception\LocalizedException(__('ECC certificate file is empty.'));
            }

            if (isset($_FILES['groups']['name'][$this->getGroupId()]['fields'][$this->getField()]['value'])) {
                $filename = $_FILES['groups']['name'][$this->getGroupId()]['fields'][$this->getField()]['value'];
            } else if (isset($_FILES['Epicor_Comm']['name'][$this->getGroupId()][$this->getField()])) {
                $filename = $_FILES['Epicor_Comm']['name'][$this->getGroupId()][$this->getField()];
            }

            $this->setValue($filename);

            //M1 > M2 Translation Begin (Rule p2-5.5)
            /*// Delete old file if exists
            if ($file->fileExists(Mage::getBaseDir() . DS . $value))
                $file->rm(Mage::getBaseDir() . DS . $value);

            // Delete file if it already exists
            if ($file->fileExists(Mage::getBaseDir() . DS . $filename))
                $file->rm(Mage::getBaseDir() . DS . $filename);

            //Move file from temp
            $file->mv($tmpPath, Mage::getBaseDir() . DS . $filename);

            //Set correct file permssions
            chmod(Mage::getBaseDir() . DS . $filename, 0660);*/

            $licenseFileDirPath = $this->directoryList->getPath('pub').DIRECTORY_SEPARATOR;
            // Delete old file if exists
            if (is_array($value)) {
                // Delete old file if exists
                if ($file->fileExists($licenseFileDirPath . $value['value'])) {
                    $file->rm($licenseFileDirPath . $value['value']);
                }
            } else {
                // Delete old file if exists (on uploading the license file from quick start page)
                if ($file->fileExists($licenseFileDirPath . $value)) {
                    $file->rm($licenseFileDirPath . $value);
                }
            }

            // Delete file if it already exists
            if ($file->fileExists($licenseFileDirPath . $filename))
                $file->rm($licenseFileDirPath . $filename);

            //Move file from temp
            $file->mv($tmpPath, $licenseFileDirPath . $filename);

            //Set correct file permssions
            chmod($licenseFileDirPath . $filename, 0660);
            //M1 > M2 Translation End
        }
        return $this;
    }

    /**
     * Process object after delete data
     *
     * @return \Epicor\Common\Model\Config\Backend\Cert
     */
    protected function _afterDelete()
    {
        $filename = $this->getValue();

        $file = $this->ioFileFactory->create();
        $file->open();
        //M1 > M2 Translation Begin (Rule p2-5.5)
        //$file->rm(Mage::getBaseDir() . DS . $filename);
        $newFilePath =  $this->directoryList->getPath('pub') . DIRECTORY_SEPARATOR . $filename;
        $oldFilePath = $this->directoryList->getRoot() . DIRECTORY_SEPARATOR . $filename;

        if(file_exists($newFilePath)){
            $filename = $newFilePath;
        }
        if(file_exists($oldFilePath)){
            $filename = $oldFilePath;
        }

        $file->rm($filename);
        //M1 > M2 Translation End

        $this->setValue('');
        return $this;
    }

    /**
     * Process object after save data
     *
     * @return \Epicor\Common\Model\Config\Backend\Cert
     */
    public function afterSave()
    {
        $this->registry->register('check_licensing_config', true);
        return parent::afterSave();
    }

}
