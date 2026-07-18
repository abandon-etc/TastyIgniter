<?php

declare(strict_types=1);

namespace App\Delivery\Exceptions;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

final class DeliveryUnavailableException extends UnprocessableEntityHttpException
{
    public function __construct()
    {
        parent::__construct('Delivery is currently unavailable. Please choose pickup.');
    }
}
