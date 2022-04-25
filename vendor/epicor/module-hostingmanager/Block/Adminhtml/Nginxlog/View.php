<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\HostingManager\Block\Adminhtml\Nginxlog;

use Epicor\Common\Helper\Data;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;
use Epicor\Common\Model\LogViewFactory;

/**
 * Nginx log view block
 *
 * @category   Epicor
 * @package    Epicor_HostingManager
 * @author     Epicor Websales Team
 */
class View extends \Magento\Backend\Block\Widget\Container
{
    private $_logFileDir = '';
    private $_logFileName = false;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var Data
     */
    protected $commonHelper;
    /** @var $logView \Epicor\Common\Model\LogView */
    private $logView;

    public function __construct(
        LogViewFactory $logViewFactory,
        Context $context,
        Registry $registry,
        Data $commonHelper,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->commonHelper = $commonHelper;

        parent::__construct(
            $context, $data
        );

      
        $this->_logFileDir = $this->getLogFilePath();
        $this->_logFileName = $this->registry->registry('log_filename');
        $this->logView = $logViewFactory->create([
            'filePath' => $this->getFilepath(),
            'logType' => 'nginx',
            'fileName' => $this->_logFileName
        ]);
        $this->_headerText = 'Viewing ' . $this->_logFileName;

        $this->addButton('back', array(
            'label' => __('Back'),
            'onclick' => 'setLocation(\'' . $this->getUrl('*/*/index') . '\')',
            'class' => 'back',
        ), -1);
    }

    public function getLogFileContents()
    {
        $this->logView->getLogFileContents();
    }

    private function getLogFilePath()
    {
        if (!$this->_logFileDir) {
            $this->_logFileDir = DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'log'
                . DIRECTORY_SEPARATOR . 'nginx' . DIRECTORY_SEPARATOR;
        }

        return $this->_logFileDir;
    }

    public function getFilepath()
    {
        return $this->_logFileDir . $this->_logFileName;
    }

    public function getFilename()
    {
        return $this->_logFileName;
    }

    public function getFiledate()
    {
        $helper = $this->commonHelper;
        $date = date('Y-m-d H:i:s', @filemtime($this->_logFileDir . $this->_logFileName));
        return $helper->getLocalDate($date,\IntlDateFormatter::MEDIUM, true);
    }

}

