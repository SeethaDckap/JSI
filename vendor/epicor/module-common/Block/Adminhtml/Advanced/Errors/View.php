<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Advanced\Errors;


/**
 * Error report view
 * 
 * @category   Epicor
 * @package    Epicor_Common
 * @author     Epicor Websales Team
 */
class View extends \Magento\Backend\Block\Widget\Container
{

    private $_fileDir = '';
    private $_fileName = false;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Common\Helper\Data
     */
    protected $commonHelper;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $directoryList;

    /**
     *@var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezoneInterface;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        array $data = []
    )
    {
        $this->registry = $registry;
        $this->commonHelper = $commonHelper;
        $this->directoryList = $directoryList;
        $this->timezoneInterface = $context->getLocaleDate();
        parent::__construct(
            $context,
            $data
        );

        if (!$this->hasData('template')) {
            $this->setTemplate('Magento_Backend::widget/form/container.phtml');
        }

        //M1 > M2 Translation Begin (Rule p2-5.5)
        //$this->_fileDir = $logFilePath = Mage::getBaseDir('var') . DS . 'report' . DS;
        $this->_fileDir = $logFilePath = $this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR) . DIRECTORY_SEPARATOR . 'report' . DIRECTORY_SEPARATOR;
        //M1 > M2 Translation End
//        $this->_fileName = $this->registry->registry('report_filename');
//        $this->_headerText = 'Viewing report ' . $this->_fileName;

        $this->addButton('back', array(
            'label' => __('Back'),
            'onclick' => 'setLocation(\'' . $this->getUrl('*/*/index') . '\')',
            'class' => 'back',
            ), -1);
    }

    public function getFilepath()
    {
        return $this->_fileDir . $this->getFilename();
    }

    public function getFilename()
    {
        return $this->registry->registry('report_filename');
    }

    public function getFiledate()
    {
        $time = date('Y-m-d H:i:s', filemtime($this->_fileDir . $this->getFilename()));
        $formattedDate = $this->timezoneInterface->formatDate($time, \IntlDateFormatter::MEDIUM, true);

        return $formattedDate;
    }
}
