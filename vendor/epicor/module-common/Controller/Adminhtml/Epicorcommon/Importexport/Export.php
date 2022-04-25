<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Importexport;

class Export extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\Importexport
{


    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $directoryList;
    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $fileFactory;

    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Magento\Framework\Session\Generic $generic, 
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Epicor\Common\Helper\Context $commonHelper
    )
    {
        $this->fileFactory = $fileFactory;
        $this->directoryList = $commonHelper->getDirectoryList();
        $this->logger = $commonHelper->getLogger();
        parent::__construct($context,$generic, $backendAuthSession, $commonHelper);
    }

    
    public function execute()
    {

        $mappingTablesArray = json_decode(base64_decode($this->getRequest()->getParam('mappingTablesArray')), true);
        $selectedTables = $this->getRequest()->getParam('mapping_row');
        if (is_array($selectedTables)) {
            try {
            $this->_mappingTables = array_intersect_key($mappingTablesArray, $selectedTables);

            //M1 > M2 Translation Begin (Rule p2-5.5)
            //$this->_backupFolder = Mage::getBaseDir('var') . '/' . 'backups' . '/' . 'epicor_comm_settings' . '/' . date('Y-m-d h-i-s') . DS;
            $this->_backupFolder = $this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR) . '/' . 'backups' . '/' . 'epicor_comm_settings' . '/' . date('Y-m-d h-i-s') . DIRECTORY_SEPARATOR;
            //M1 > M2 Translation End
            mkdir($this->_backupFolder, 0777, true);                  // backup folder will never previously exist
            if (!is_dir($this->_backupFolder)) {
                $this->messageManager->addErrorMessage(__(' Error: Unable to create backup directory'));
                $this->_redirectReferer();
            } else {

                $this->backupTables();

                $backupFile = $this->_backupFolder . "EpicorCommExport.txt";
                if (!file_put_contents($backupFile, $this->_serializedFinal)) {
                    $this->messageManager->addErrorMessage(__(' Error: Backup file did not save successfully'));
                    $this->_redirectReferer();
                } else {
                    $fileName = 'epicor_comm_settings_' . date('Y-m-d h-i-s') . '.txt';
                    $content = $this->_serializedFinal;
                    return $this->fileFactory->create(
                            $fileName,
                            $content,
                            \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR,
                            'text/plain'
                    );
                   // $this->_prepareDownloadResponse('epicor_comm_settings_' . date('Y-m-d h-i-s') . '.txt', $this->_serializedFinal, 'text/plain');
                    //Mage::getSingleton('core/session')->addSuccess(Mage::helper('epicor_common')->__('Backup created successfully'));
                }
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->_redirect('*/*/');
        }
      }else{
          $this->messageManager->addNoticeMessage(__('Select at least one mapping table under Backup Selected Tables option.'));
          $this->_redirect('*/*/');
      }
    }

}
