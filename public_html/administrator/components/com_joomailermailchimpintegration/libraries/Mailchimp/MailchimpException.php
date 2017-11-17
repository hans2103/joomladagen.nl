<?php

class MailchimpException extends Exception {

    public function __construct($message, $code = 0, $detail = '', Exception $previous = null) {
        $this->code = $code;
        $message = $this->errorMessage($message, $detail);

        parent::__construct($message, $code, $previous);
    }

    public function errorMessage($message, $detail) {
            switch($this->getCode()) {
                case '401':
                    $message = JText::_('JM_INVALID_API_KEY');
                    break;

                case '406':
                    $message = JText::_('JM_CAMPAIGN_INVALIDCONTENT');
                    break;

                default:
                    $message = JText::_('JM_BAD_REQUEST');
        }

        return $message . ($detail ? ' (' . $detail . ')' : '');
    }
}
