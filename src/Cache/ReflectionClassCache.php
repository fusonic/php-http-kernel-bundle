<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

// Licensed under the MIT License. See LICENSE file in the project root for license information.

declare(strict_types=1);

namespace Fusonic\HttpKernelBundle\Cache;

final class ReflectionClassCache
{
    /**
     * @var array<class-string, \ReflectionClass<object>>
     */
    private static array $reflectionClassCache = [];

    /**
     * @return \ReflectionClass<object>|null
     */
    public static function getReflectionClass(string $className): ?\ReflectionClass
    {
        if (isset(self::$reflectionClassCache[$className])) {
            return self::$reflectionClassCache[$className];
        }

        if (!class_exists($className)) {
            return null;
        }

        $reflectionClass = new \ReflectionClass($className);

        self::$reflectionClassCache[$className] = $reflectionClass;

        return $reflectionClass;
    }
}
