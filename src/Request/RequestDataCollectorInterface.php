<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\HttpKernelBundle\Request;

use Symfony\Component\HttpFoundation\Request;

/**
 * Collect the data used for the model from the request object.
 */
interface RequestDataCollectorInterface
{
    /**
     * @param class-string $className
     *
     * @return array<mixed>
     */
    public function collect(Request $request, string $className): array;
}
