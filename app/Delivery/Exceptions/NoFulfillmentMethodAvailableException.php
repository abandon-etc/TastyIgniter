<?php

declare(strict_types=1);

namespace App\Delivery\Exceptions;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

final class NoFulfillmentMethodAvailableException extends UnprocessableEntityHttpException
{
    public function __construct()
    {
        parent::__construct('No fulfillment method is currently available.');
    }
}
