<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Controller\Account;

class Encodedata extends \Epicor\Common\Controller\Account
{

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $cache;

    /**
     * @var \Epicor\Common\Helper\Data
     */
    protected $commonHelper;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\CacheInterface $cache,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->cache = $cache;
        $this->commonHelper = $commonHelper;
        $this->request = $request;
        parent::__construct(
            $context
        );
    }



    public function execute()
    {
        $this->cache->clean();  // clear cache (chagne to clear message cache)  
        $helper = $this->commonHelper;
        $data = $this->request->getParams();
        $non_json_data = json_decode($data['jsondata']);
        $string_data = implode(",", $non_json_data);
        $encoded_data_array = $helper->urlEncode($string_data);
        $encoded_data = explode(",,", $encoded_data_array);
        echo $encoded_data[0];
    }

    }
