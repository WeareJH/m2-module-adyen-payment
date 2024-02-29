<?php

declare(strict_types=1);

namespace Jh\AdyenPayment\Api\Data;

interface ConfigInterface
{
    /**
     * @return string
     */
    public function getConfig(): string;
}