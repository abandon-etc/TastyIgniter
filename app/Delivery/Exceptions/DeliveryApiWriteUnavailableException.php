<?php

declare(strict_types=1);

namespace App\Delivery\Exceptions;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

final class DeliveryApiWriteUnavailableException extends UnprocessableEntityHttpException
{
    public function __construct()
    {
        parent::__construct('Delivery orders cannot be written through this API.');
    }
}
