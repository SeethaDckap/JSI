<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Supplierconnect
 * @subpackage Block
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Supplierconnect\Block\Customer\Rfqs\Details\Attachments\Renderer;


use Epicor\Common\Helper\File;
use Magento\Backend\Block\Context;
use Magento\Framework\DataObject;
use Magento\Framework\Registry;

/**
 * Attachment filename field renderer class.
 */
class Filename extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * Registry class.
     *
     * @var Registry
     */
    protected $registry;

    /**
     * File helper.
     *
     * @var File
     */
    protected $commonFileHelper;


    /**
     * Constructor function
     *
     * @param Context  $context          Context class.
     * @param Registry $registry         Registry class.
     * @param File     $commonFileHelper File helper.
     * @param array    $data             Data.
     */
    public function __construct(
        Context $context,
        Registry $registry,
        File $commonFileHelper,
        array $data=[]
    ) {
        $this->registry         = $registry;
        $this->commonFileHelper = $commonFileHelper;
        parent::__construct(
            $context,
            $data
        );

    }//end __construct()


    /**
     * Render function.
     *
     * @param DataObject $row Row object.
     *
     * @return string
     */
    public function render(DataObject $row)
    {
        $key    = 'existing';
        $helper = $this->commonFileHelper;

        $index = $this->getColumn()->getIndex();
        $value = $row->getData($index);

        $url  = $helper->getFileUrl($row->getWebFileId(), $row->getErpFileId(), $row->getFilename(), $row->getUrl());
        $html = $value.'<a href="'.$url.'" target="_blank" class="attachment_view">View</a>';

        if ($this->registry->registry('rfq_editable') && $row->getWebFileId()) {
            $html .= ' | '.__('Update File').': <input type="file" name="attachments['.$key.']['.$row->getUniqueId().']['.$index.']" value="'.$value.'" class="lines_'.$index.'"/>';
        }

        return $html;

    }//end render()


}//end class
