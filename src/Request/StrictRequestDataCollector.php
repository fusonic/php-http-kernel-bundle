<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\HttpKernelBundle\Request;

use Fusonic\HttpKernelBundle\Controller\RequestDtoResolver;
use Fusonic\HttpKernelBundle\Request\BodyParser\FormRequestBodyParser;
use Fusonic\HttpKernelBundle\Request\BodyParser\JsonRequestBodyParser;
use Fusonic\HttpKernelBundle\Request\BodyParser\RequestBodyParserInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

readonly class StrictRequestDataCollector implements RequestDataCollectorInterface
{
    /**
     * @var array<string, RequestBodyParserInterface>
     */
    private array $requestBodyParsers;

    /**
     * @param array<string, RequestBodyParserInterface>|null $requestBodyParsers
     */
    public function __construct(
        /**
         * Whether to force values from route params to be converted to integers if they look like integers.
         *
         *   Example: if you route contains '1' as a value for a parameter, and you intended it to be a string,
         *   you should set this option to 'false'.
         */
        private bool $forceRouteParamsIntegers = true,

        ?array $requestBodyParsers = null,
    ) {
        $this->requestBodyParsers = $requestBodyParsers ?? [
            'json' => new JsonRequestBodyParser(),
            'default' => new FormRequestBodyParser(),
        ];
    }

    public function collect(Request $request): array
    {
        $routeParameters = $this->getRouteParams($request);

        if (\in_array($request->getMethod(), RequestDtoResolver::METHODS_WITH_STRICT_TYPE_CHECKS, true)) {
            return $this->mergeRequestData($this->parseRequestBody($request), $routeParameters);
        }

        return $this->mergeRequestData($request->query->all(), $routeParameters);
    }

    /**
     * @return array<string, mixed>
     */
    protected function getRouteParams(Request $request): array
    {
        $params = $request->attributes->get('_route_params', []);

        if ($this->forceRouteParamsIntegers) {
            foreach ($params as $key => $param) {
                $value = filter_var($param, \FILTER_VALIDATE_INT, \FILTER_NULL_ON_FAILURE);
                $params[$key] = $value ?? $param;
            }
        }

        return $params;
    }

    /**
     * @param array<mixed>         $data
     * @param array<string, mixed> $routeParameters
     *
     * @return array<string, mixed>
     */
    protected function mergeRequestData(array $data, array $routeParameters): array
    {
        if (\count($keys = array_intersect_key($data, $routeParameters)) > 0) {
            throw new BadRequestHttpException(\sprintf('Parameters (%s) used as route attributes can not be used in the request body or query parameters.', implode(', ', array_keys($keys))));
        }

        return array_merge($data, $routeParameters);
    }

    /**
     * @return mixed[]
     */
    protected function parseRequestBody(Request $request): array
    {
        $requestBodyParser = $this->requestBodyParsers[$request->getContentTypeFormat()] ?? null;

        if (null === $requestBodyParser) {
            $requestBodyParser = $this->requestBodyParsers['default'];
        }

        return $requestBodyParser->parse($request);
    }
}
