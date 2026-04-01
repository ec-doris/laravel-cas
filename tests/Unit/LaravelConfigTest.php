<?php

declare(strict_types=1);

namespace EcDoris\LaravelCas\Tests\Unit;

use EcDoris\LaravelCas\Config\Laravel;
use EcDoris\LaravelCas\Tests\TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;

class LaravelConfigTest extends TestCase
{
    public function test_it_converts_known_route_names_to_absolute_urls(): void
    {
        $config = new Laravel(new ParameterBag([
            'base_url' => 'https://webgate.ec.europa.eu/cas',
            'protocol' => [
                'serviceValidate' => [
                    'default_parameters' => [
                        'service' => 'laravel-cas-callback',
                        'pgtUrl' => 'laravel-cas-proxy-callback',
                    ],
                ],
            ],
        ]));

        $properties = $config->jsonSerialize();

        $this->assertSame(
            'http://localhost/cas/callback',
            $properties['protocol']['serviceValidate']['default_parameters']['service']
        );
        $this->assertSame(
            'http://localhost/proxy/callback',
            $properties['protocol']['serviceValidate']['default_parameters']['pgtUrl']
        );
    }

    public function test_it_keeps_unknown_route_names_unchanged(): void
    {
        $config = new Laravel(new ParameterBag([
            'base_url' => 'https://webgate.ec.europa.eu/cas',
            'protocol' => [
                'serviceValidate' => [
                    'default_parameters' => [
                        'service' => 'missing-route-name',
                    ],
                ],
            ],
        ]));

        $properties = $config->jsonSerialize();

        $this->assertSame(
            'missing-route-name',
            $properties['protocol']['serviceValidate']['default_parameters']['service']
        );
    }
}
