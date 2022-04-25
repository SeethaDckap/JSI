<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Html\Links;

use Magento\Framework\App\DefaultPathInterface;
use Magento\Framework\View\Element\Template\Context;
use \Epicor\AccessRight\Helper\Data as AccessRightHelper;
class Current extends \Magento\Framework\View\Element\Html\Link\Current
{
    /**
     * Search redundant /index and / in url
     */
    private const REGEX_INDEX_URL_PATTERN = '/(\/index|(\/))+($|\/$)/';

    const FRONTEND_RESOURCE = 'Epicor_Checkout::catalog_advance_search';

    /**
     * @var \Epicor\AccessRight\Model\Authorization
     */
    protected $_accessauthorization;

    /**
     * Constructor.
     *
     * @param Context              $context       Context.
     * @param DefaultPathInterface $defaultPath   DefaultPathInterface.
     * @param AccessRightHelper    $authorization AccessRightHelper.
     * @param array                $data          Array.
     */
    public function __construct(
        Context $context,
        DefaultPathInterface $defaultPath,
        AccessRightHelper $authorization,
        array $data = []
    ) {
        $this->_accessauthorization = $authorization->getAccessAuthorization();
        parent::__construct($context, $defaultPath, $data);
    }//end __construct()

    /**
     * Render block HTML.
     *
     * @return string
     */
    protected function _toHtml()
    {
        // Validate accessRights.
        if (!$this->_accessauthorization->isAllowed(self::FRONTEND_RESOURCE)) {
            return '';
        }

        return parent::_toHtml();
    }//end _toHtml()
}
