<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\HttpKernelBundle\Request\UrlParser;

interface UrlParserInterface
{
    /**
     * Determine whether a value is considered null. For example: if an empty string should be considered as null.
     */
    public function isNull(string $value): bool;

    public function parseInteger(string $value): ?int;

    public function parseFloat(string $value): ?float;

    public function parseBoolean(string $value): ?bool;

    public function parseString(string $value): ?string;

    /**
     * What to do in case one of the types is invalid. For example: throw an exception or do nothing and
     * let a later validation step handle this.
     *
     * @param class-string $className
     */
    public function handleFailure(string $attribute, string $className, string $expectedType, string $value, string $propertyPath): void;
}