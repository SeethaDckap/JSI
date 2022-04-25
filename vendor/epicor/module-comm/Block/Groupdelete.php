<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Block;

use Magento\Framework\App\Filesystem\DirectoryList;

class Groupdelete extends \Magento\Framework\View\Element\Template {

    public function __construct(\Magento\Framework\View\Element\Template\Context $context, \Magento\Framework\App\Filesystem\DirectoryList $directory_list, array $data = []) {
        parent::__construct($context, $data);
        $this->directory_list = $directory_list;
    }

    public function checkProductCheckLogFileExistOrNot() {
        $productchecklog = $this->directory_list->getPath('log') . '/productcheck.log';
        if (file_exists($productchecklog)) {
            return true;
        } else {
            return false;
        }
    }

}
