<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Lists
 * @subpackage Block
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Lists\Block\Adminhtml\Import\View;

use Magento\Backend\Block\Template\Context;
use Epicor\Lists\Api\ImportRepositoryInterface;
use Epicor\Lists\Model\Import;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Framework\Stdlib\DateTime\TimezoneInterfaceFactory as TimeZone;

/**
 * Class GenericButton
 *
 */
class GenericInfo extends \Magento\Backend\Block\Template
{

    /**
     * Import repository interface.
     *
     * @var ImportRepositoryInterface
     */
    protected $importRepository;

    /**
     * Serializer interface instance.
     *
     * @var JsonSerializer
     */
    protected $serializer;

    /**
     * @var TimezoneInterfaceFactory
     */
    protected $timezone;

    /**
     * GenericInfo constructor.
     *
     * @param Context                   $context
     * @param ImportRepositoryInterface $importRepository
     * @param JsonSerializer            $jsonSerializer
     * @param TimeZone                  $timezoneInterface
     * @param array                     $data
     */
    public function __construct(
        Context $context,
        ImportRepositoryInterface $importRepository,
        JsonSerializer $jsonSerializer,
        TimeZone $timezoneInterface,
        array $data = []
    ) {
        $this->importRepository = $importRepository;
        $this->serializer = $jsonSerializer;
        $this->timezone = $timezoneInterface;
        parent::__construct($context, $data);
    }//end __construct()

    /**
     * @return Import
     */
    public function getImport()
    {
        return $this->importRepository->loadEntity();
    }

    /**
     * @param string $messages
     *
     * @return array
     */
    public function getMessages($messages)
    {
        return $this->serializer->unserialize($messages);
    }

    /**
     * Change date time zone
     *
     * @param $createdAt
     *
     * @return mixed
     * @throws \Exception
     */
    public function getFormatDateTime($createdAt)
    {
        $timezone = $this->timezone->create();
        $newDate = $timezone->date(new \DateTime($createdAt));

        return $newDate->format('M j, Y g:i:s A');
    }

}//end class
