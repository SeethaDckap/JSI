<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Advanced\Syslog;

use Epicor\Common\Model\LogViewFactory;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Syslog edit block
 *
 * @category   Epicor
 * @package    Epicor_Common
 * @author     Epicor Websales Team
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $_varDirectory;
    protected $_filesystem;
    /** @var $logView \Epicor\Common\Model\LogView */
    private $logView;

    public function __construct(
        LogViewFactory $logViewFactory,
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->_filesystem = $context->getFilesystem();
        $this->_controller = 'adminhtml_advanced_syslog';
        $this->_blockGroup = 'Epicor_Common';

        parent::__construct(
            $context,
            $data
        );

        $this->removeButton('reset');
        $this->removeButton('save');
        $this->logView = $logViewFactory->create([
            'filePath' => $this->getSystemLogFilePath(),
            'logType' => 'system',
            'fileName' => $this->systemLogFileName()
        ]);
    }

    /**
     * Prepare form Html. call the phtm file with form.
     *
     * @return string
     */
    public function getFormHtml()
    {
        // get the current form as html content.
        $html = parent::getFormHtml();
        //Append the phtml file after the form content.
        $html .= $this->setTemplate('Epicor_Common::epicor_common/advanced/syslog/view/details.phtml')->toHtml();
        
        return $html;
    }

    public function systemLogFileName()
    {
        $file = $this->registry->registry('sysLogFilename');
        
        return $file;
    }

    public function getSystemLogFilePath()
    {
        return $this->_filesystem->getDirectoryRead(DirectoryList::LOG)->getAbsolutePath() . $this->systemLogFileName();
    }

    public function getLogFileTime()
    {
        $fileInformation = stat($this->getSystemLogFilePath());
        $fileModificationTime = $fileInformation['mtime'];

        return date("F d Y H:i:s", $fileModificationTime);
    }

    public function getLogFileContents()
    {
        $this->logView->getLogFileContents();
    }
}
