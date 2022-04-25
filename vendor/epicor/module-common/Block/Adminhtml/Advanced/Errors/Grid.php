<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Advanced\Errors;


/**
 * Error report Grid
 * 
 * @category   Epicor
 * @package    Epicor_Common
 * @author     Epicor Websales Team
 */
class Grid extends \Epicor\Common\Block\Generic\Listing\Search
{

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $directoryList;
    
    protected $encoder;
    protected $encryptor;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Common\Model\Message\CollectionFactory $commonMessageCollectionFactory,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
        \Epicor\Common\Model\GridConfigOptionsModelReader $configOptionsModelReader,
        \Epicor\Common\Block\Generic\Listing\ColumnRendererReader $columnRendererReader,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Url\EncoderInterface $encoder,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        array $data = []
    )
    {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->directoryList = $directoryList;
        $this->encoder = $encoder;
        $this->encryptor =$encryptor;
        parent::__construct(
            $context,
            $backendHelper,
            $commonMessageCollectionFactory,
            $commonHelper,
            $frameworkHelperDataHelper,
            $configOptionsModelReader,
            $columnRendererReader,
            $data
        );

        $this->setId('advancedErrorsGrid');
        $this->setDefaultSort('last_modified');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);

        $this->setCustomColumns($this->_getColumns());
        $this->setExportTypeCsv(false);
        $this->setExportTypeXml(false);

        $this->setMessageBase('epicor_common');
        $this->setIdColumn('id');

        $this->setCacheDisabled(true);

        $this->setCustomData($this->getCustomData());
    }

    public function getCustomData()
    {
        //M1 > M2 Translation Begin (Rule p2-5.5)
        //$reportFileDir = Mage::getBaseDir('var') . DS . 'report' . DS;
        $reportFileDir = $this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR) . DIRECTORY_SEPARATOR . 'report' . DIRECTORY_SEPARATOR;
        //M1 > M2 Translation End

        $files = array();

        if (file_exists($reportFileDir)) {
            $reportFiles = scandir($reportFileDir);

            foreach ($reportFiles as $file) {
                if (!in_array($file, array('.', '..'))) {
                    $files[] = $this->dataObjectFactory->create(array('data' => array(
                        'filename' => $file,
                        'last_modified' => date('Y-m-d H:i:s', filemtime($reportFileDir . $file)),
                    )));
                }
            }
        }

        return $files;
    }

    protected function _getColumns()
    {
        $columns = array();

        $columns['filename'] = array(
            'header' => __('Name'),
            'align' => 'left',
            'index' => 'filename'
        );

        $columns['last_modified'] = array(
            'header' => __('Last Modified'),
            'align' => 'left',
            'index' => 'last_modified',
            'type' => 'datetime',
        );

        return $columns;
    }

    public function getRowUrl($row)
    {
        $report = $this->encoder->encode($this->encryptor->encrypt($row->getFilename()));
        return $this->getUrl('*/*/view', array('report' => $report));
    }
}
