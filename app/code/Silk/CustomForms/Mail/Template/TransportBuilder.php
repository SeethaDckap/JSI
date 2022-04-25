<?php

namespace Silk\CustomForms\Mail\Template;

class TransportBuilder extends \Magento\Framework\Mail\Template\TransportBuilder
{
    // add email attachment function
    // TODO: rewrite zend code in magento 2.3
    public function addAttachment($file, $name, $type) 
    {
        if (!empty($file) && file_exists($file)) {
            $this->message->setBodyAttachment(
                file_get_contents($file),
                basename($name),
                $type
            );
        }

        return $this;
    }

    protected function prepareMessage()
    {
        parent::prepareMessage();
        $this->message->setPartsToBody();
        return $this;
    }

    // Clears the sender from the mail
    public function clearFrom()
    {
        $this->message->clearFrom('From');
        return $this;
    }
    

    public function clearSubject()
    {
        $this->message->clearSubject();
        return $this;
    }

    public function clearMessageId()
    {
        $this->message->clearMessageId();
        return $this;
    }

    public function clearBody()
    {
        $this->message->setParts([]);
        return $this;
    }

    public function clearRecipients()
    {
        $this->message->clearRecipients();
        return $this;
    }

    /**
     * Clear header from the message
     *
     * @param string $headerName
     * @return Zend_Mail Provides fluent inter
     */
    public function clearHeader($headerName)
    {
        if (isset($this->_headers[$headerName])) {
            unset($this->_headers[$headerName]);
        }
        return $this;
    }
}
