<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Punchout
 * @subpackage Block
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Block\Adminhtml\Import\View;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;

/**
 * Class GenericButton
 *
 */
class GenericButton
{
    /**
     * Url Builder
     *
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * Request interface.
     *
     * @var RequestInterface
     */
    private $request;


    /**
     * GenericButton constructor.
     *
     * @param Context $context Context.
     */
    public function __construct(
        Context $context
    ) {
        $this->request = $context->getRequest();
        $this->urlBuilder = $context->getUrlBuilder();

    }//end __construct()


    /**
     * Generate url by route and parameters
     *
     * @param string $route  Route name.
     * @param array  $params Parameters.
     *
     * @return string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->urlBuilder->getUrl($route, $params);

    }//end getUrl()


    /**
     * Check where button can be rendered
     *
     * @param string $name Name.
     *
     * @return string
     */
    public function canRender($name)
    {
        return $name;

    }//end canRender()


    /**
     * Retrieve request object
     *
     * @return RequestInterface
     */
    public function getRequest()
    {
        return $this->request;

    }//end getRequest()


}//end class
