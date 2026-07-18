<?php

declare(strict_types=1);

namespace App\Delivery\Exceptions;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

final class CollectionUnavailableException extends UnprocessableEntityHttpException
{
    public function __construct()
    {
        parent::__construct('Pickup is currently unavailable. Please choose delivery.');
    }
}
