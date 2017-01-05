<?php
declare(strict_types = 1);

namespace Roave\DoctrineSimpleCache;

use Psr\SimpleCache\InvalidArgumentException as PsrInvalidArgumentException;

final class InvalidArgumentException extends \InvalidArgumentException implements PsrInvalidArgumentException
{
    public static function invalidKeyFormat($key) : self
    {
        return new self(sprintf('The given cache key "%s" contains at least one unsupported character', $key));
    }
}
