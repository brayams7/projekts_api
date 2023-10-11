<?php

namespace App\Exceptions;

use Exception;

class AttachmentTypeNotFoundException extends Exception
{
    /**
     * Create a new exception instance.
     *
     * @param string $message
     */
    public function __construct(string $message = 'Attachment type not found.')
    {
        parent::__construct($message);
    }
}