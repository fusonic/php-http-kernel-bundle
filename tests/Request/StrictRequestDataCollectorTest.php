<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

// Licensed under the MIT License. See LICENSE file in the project root for license information.

declare(strict_types=1);

namespace Fusonic\HttpKernelBundle\Tests\Normalizer;

use Fusonic\HttpKernelBundle\Request\StrictRequestDataCollector;
use Fusonic\HttpKernelBundle\Tests\Dto\DummyClassB;
use Fusonic\HttpKernelBundle\Tests\Dto\RouteParameterDto;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;

class StrictRequestDataCollectorTest extends TestCase
{
    public function testInvalidFloat(): void
    {
        $urlParser = new StrictRequestDataCollector(strictRouteParams: true, strictQueryParams: true);
        $attributes = [
            '_route_params' => [
                'int' => '5',
                'float' => 'invalid9.99',
                'string' => 'foobar',
                'bool' => 'true',
            ],
        ];
        $request = new Request([], [], $attributes);

        $ex = null;

        try {
            $urlParser->collect(
                $request,
                RouteParameterDto::class
            );
        } catch (NotNormalizableValueException $ex) {
        }

        self::assertNotNull($ex);
        self::assertSame(
            'The type of the "float" attribute for class "Fusonic\HttpKernelBundle\Tests\Dto\RouteParameterDto" must be float ("invalid9.99" given).',
            $ex->getMessage()
        );
    }

    public function testInvalidBoolean(): void
    {
        $urlParser = new StrictRequestDataCollector(strictRouteParams: true, strictQueryParams: true);
        $attributes = [
            '_route_params' => [
                'int' => '5',
                'float' => '9.99',
                'string' => 'foobar',
                'bool' => 'haha',
            ],
        ];
        $request = new Request([], [], $attributes);

        $ex = null;

        try {
            $urlParser->collect(
                $request,
                RouteParameterDto::class
            );
        } catch (NotNormalizableValueException $ex) {
        }

        self::assertNotNull($ex);
        self::assertSame(
            'The type of the "bool" attribute for class "Fusonic\HttpKernelBundle\Tests\Dto\RouteParameterDto" must be bool ("haha" given).',
            $ex->getMessage()
        );
    }

    public function testInvalidInteger(): void
    {
        $urlParser = new StrictRequestDataCollector(strictRouteParams: true, strictQueryParams: true);
        $attributes = [
            '_route_params' => [
                'int' => 'aa5',
                'float' => '9.99',
                'string' => 'foobar',
                'bool' => 'haha',
            ],
        ];
        $request = new Request([], [], $attributes);

        $ex = null;

        try {
            $urlParser->collect(
                $request,
                RouteParameterDto::class
            );
        } catch (NotNormalizableValueException $ex) {
        }

        self::assertNotNull($ex);
        self::assertSame(
            'The type of the "int" attribute for class "Fusonic\HttpKernelBundle\Tests\Dto\RouteParameterDto" must be int ("aa5" given).',
            $ex->getMessage()
        );
    }

    public function testNullable(): void
    {
        $urlParser = new StrictRequestDataCollector(strictRouteParams: true, strictQueryParams: true);
        $attributes = [
            '_route_params' => [
                'int' => '5',
                'float' => '',
                'string' => 'foobar',
                'bool' => 'false',
            ],
        ];
        $request = new Request([], [], $attributes);

        $data = $urlParser->collect(
            $request,
            RouteParameterDto::class
        );

        self::assertNull($data['float']);
    }

    public function testNonStrict(): void
    {
        $urlParser = new StrictRequestDataCollector(strictRouteParams: false, strictQueryParams: false);
        $attributes = [
            '_route_params' => [
                'requiredArgument' => 'a',
            ],
        ];
        $request = new Request([
            'someProperty' => '123',
        ], [], $attributes);

        $data = $urlParser->collect(
            $request,
            DummyClassB::class
        );

        self::assertSame('a', $data['requiredArgument']);
        self::assertSame('123', $data['someProperty']);
    }
}
