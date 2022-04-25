<?php
namespace Silk\CustomForms\Mail;

use Zend\Mime\Mime;
use Zend\Mime\PartFactory;
use Zend\Mail\MessageFactory as MailMessageFactory;
use Zend\Mime\MessageFactory as MimeMessageFactory;

class Message implements \Magento\Framework\Mail\MailMessageInterface
{

    private $zendMessage;

    protected $mimePartFactory;

    protected $mimeMessageFactory;

    protected $mimePart = [];

    public function __construct(
        PartFactory $mimePartFactory, 
        MimeMessageFactory $mimeMessageFactory
    ){
        $this->mimePartFactory = $mimePartFactory;
        $this->mimeMessageFactory = $mimeMessageFactory;
        $this->zendMessage = new \Zend\Mail\Message();
        $this->zendMessage->setEncoding('utf-8');
    }

    public function setBodyText($content)
    {
        $text = $this->mimePartFactory->create();
        $text->setContent($content)
            ->setType(Mime::TYPE_TEXT)
            ->setCharset($this->zendMessage->getEncoding());
        $this->mimePart[] = $text;
        return $this;
    }

    public function setBodyHtml($content)
    {
        $html = $this->mimePartFactory->create();
        $html->setContent($content)
            ->setType(Mime::TYPE_HTML)
            ->setCharset($this->zendMessage->getEncoding());
        $this->mimePart[] = $html;
        return $this;
    }

    public function setBodyAttachment($fileContent, $fileName, $fileType)
    {
        $attachment = $this->mimePartFactory->create();
        $attachment->setContent($fileContent)
            ->setType($fileType)
            ->setFileName($fileName)
            ->setDisposition(Mime::DISPOSITION_ATTACHMENT)
            ->setEncoding(Mime::ENCODING_BASE64);
        $this->mimePart[] = $attachment;
        return $this;
    }

    public function setPartsToBody()
    {
        $mimeMessage = $this->mimeMessageFactory->create();
        $mimeMessage->setParts($this->mimePart);
        $this->zendMessage->setBody($mimeMessage);
        return $this;
    }

    public function setBody($body)
    {
        return $this;
    }

    public function setMessageType($type)
    {
        return $this;
    }

    /* Inheritdoc from Magento\Framework\Mail\Message */

    /**
     * {@inheritdoc}
     */
    public function setSubject($subject)
    {
        $this->zendMessage->setSubject($subject);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubject()
    {
        return $this->zendMessage->getSubject();
    }

    /**
     * {@inheritdoc}
     */
    public function getBody()
    {
        return $this->zendMessage->getBody();
    }

    /**
     * {@inheritdoc}
     */
    public function setFrom($fromAddress)
    {
        $this->setFromAddress($fromAddress, null);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setFromAddress($fromAddress, $fromName = null)
    {
        $this->zendMessage->setFrom($fromAddress, $fromName);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addTo($toAddress)
    {
        $this->zendMessage->addTo($toAddress);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addCc($ccAddress)
    {
        $this->zendMessage->addCc($ccAddress);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addBcc($bccAddress)
    {
        $this->zendMessage->addBcc($bccAddress);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setReplyTo($replyToAddress)
    {
        $this->zendMessage->setReplyTo($replyToAddress);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRawMessage()
    {
        return $this->zendMessage->toString();
    }

    /**
     * Create HTML mime message from the string.
     *
     * @param string $htmlBody
     * @return \Zend\Mime\Message
     */
    private function createHtmlMimeFromString($htmlBody)
    {
        $htmlPart = new Part($htmlBody);
        $htmlPart->setCharset($this->zendMessage->getEncoding());
        $htmlPart->setType(Mime::TYPE_HTML);
        $mimeMessage = new \Zend\Mime\Message();
        $mimeMessage->addPart($htmlPart);
        return $mimeMessage;
    }

}