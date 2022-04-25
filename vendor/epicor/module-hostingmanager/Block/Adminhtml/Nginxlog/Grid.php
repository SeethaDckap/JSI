<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\HostingManager\Block\Adminhtml\Nginxlog;

use Epicor\HostingManager\Model\Host;

/**
 * Nginx log grid 
 * 
 * @category   Epicor
 * @package    Epicor_HostingManager
 * @author     Epicor Websales Team
 */
class Grid extends \Epicor\Common\Block\Generic\Listing\Search
{

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var \Epicor\Common\Block\Adminhtml\Widget\Grid\Column\Renderer\FilesizeFactory
     */
    protected $commonAdminhtmlWidgetGridColumnRendererFilesizeFactory;
    
    /**
     * @var \Magento\Framework\Url\EncoderInterface
     */
    protected $urlEncoder;
    private $siteNginxLogFileNames;
    private $host;

    public function __construct(
        Host $host,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper, 
        \Epicor\Common\Model\Message\CollectionFactory $commonMessageCollectionFactory, 
        \Epicor\Common\Helper\Data $commonHelper, 
        \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper, 
        \Epicor\Common\Model\GridConfigOptionsModelReader $configOptionsModelReader, 
        \Epicor\Common\Block\Generic\Listing\ColumnRendererReader $columnRendererReader, 
        \Magento\Framework\DataObjectFactory $dataObjectFactory, 
        \Epicor\Common\Block\Adminhtml\Widget\Grid\Column\Renderer\FilesizeFactory $commonAdminhtmlWidgetGridColumnRendererFilesizeFactory,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        array $data = []
    ){
        $this->dataObjectFactory = $dataObjectFactory;
        $this->commonAdminhtmlWidgetGridColumnRendererFilesizeFactory = $commonAdminhtmlWidgetGridColumnRendererFilesizeFactory;
        $this->urlEncoder = $urlEncoder;
        parent::__construct(
            $context, $backendHelper, $commonMessageCollectionFactory, $commonHelper, $frameworkHelperDataHelper, $configOptionsModelReader, $columnRendererReader, $data
        );
        $this->siteNginxLogFileNames = $host->getNginxLogNames();
        $this->setId('NginxlogGrid');
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
        $this->host = $host;
    }

    protected function getCustomData()
    {
        $logFileDir = DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR . 'nginx' . DIRECTORY_SEPARATOR;
        $htmlDir = DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'www' . DIRECTORY_SEPARATOR . 'html' . DIRECTORY_SEPARATOR;

        $files = array();
        if (file_exists($logFileDir)) {
            $logFiles = scandir($logFileDir);
            //$file_prefix = $this->scopeConfig->getValue('epicor_hosting/file/prefix', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $filePrefix = str_replace($htmlDir, '', BP);
            foreach ($logFiles as $file) {
                if (!in_array($file, array('.', '..'))) {
                    if ($this->isLogFileForCurrentStore($file)) {
                        $fileObj = $this->dataObjectFactory->create();
                        $fileObj->addData(array(
                            'filename' => $file,
                            'size' => filesize($logFileDir . $file),
                            'last_modified' => date('Y-m-d H:i:s', @filemtime($logFileDir . $file)),
                        ));
                        $files[] = $fileObj;
                   }
                }
            }
        }

        return $files;
    }

    private function isLogFileForCurrentStore($file) {
        if ($this->scopeConfig->getValue('web/nginxlogs/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            return true;
        } else {
            $accessOrErrors = array();
            $accessOrErrors = array_merge($this->siteNginxLogFileNames['access'], $this->siteNginxLogFileNames['error']);
            foreach ($accessOrErrors as $accessOrError) {
                if (strstr($file, $accessOrError) !== false) {
                    return true;
                }
            }
        }
        return false;
    }

    protected function _getColumns()
    {
        $columns = array();

        $columns['filename'] = array(
            'header' => __('Name'),
            'align' => 'left',
            'index' => 'filename',
            'type' => 'text'
        );

        $columns['size'] = array(
            'header' => __('Size'),
            'align' => 'left',
            'index' => 'size',
            'type' => 'number',
            'renderer' => '\Epicor\Common\Block\Adminhtml\Widget\Grid\Column\Renderer\Filesize',
            'filter' => false
        );

        $columns['last_modified'] = array(
            'header' => __('Last Modified'),
            'align' => 'left',
            'index' => 'last_modified',
            'type' => 'datetime',
        );
        $columns['action'] = array(
                   'header' => __('Action'),
                   'align' => 'left',
                   'index' => 'action',
                   'type' => 'text',
                   'filter' => false,
                   'renderer' => 'Epicor\HostingManager\Block\Adminhtml\Widget\Grid\Column\Renderer\Viewordownload',
                   'sortable' => false
               );
        return $columns;
    }

    public function getRowUrl($row)
    {
        $log = $this->urlEncoder->encode($row->getFilename());
        return $this->getUrl('*/*/view', array('filename' => $log));
    }

}
