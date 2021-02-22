<?php

namespace Klaviyo\Exception;

class KlaviyoApiException extends KlaviyoException {

  public function __construct($message, $code, $previous = null) {
        $message = 'Status Code ' . $code . ' - ' . $message;
        parent::__construct($message, $code, $previous);
    }
}
