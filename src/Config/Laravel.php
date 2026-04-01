<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ec-doris/laravel-cas
 */

declare(strict_types=1);

namespace EcDoris\LaravelCas\Config;

use EcPhp\CasLib\Configuration\Properties as PsrCasConfiguration;
use EcPhp\CasLib\Contract\Configuration\PropertiesInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

use const FILTER_VALIDATE_URL;

class Laravel implements PropertiesInterface
{
    private PropertiesInterface $cas;

    public function __construct(ParameterBag $parameterBag)
    {
        $this->cas = new PsrCasConfiguration(
            $this->routeToUrl($parameterBag->all())
        );
    }

    public function all(): array
    {
        return $this->cas->jsonSerialize();
    }

    public function jsonSerialize(): array
    {
        return $this->cas->jsonSerialize();
    }

    /**
     * Transform configured route names into absolute URLs.
     *
     * @param array<string, mixed> $properties
     *
     * @return array<string, mixed>
     */
    private function routeToUrl(array $properties): array
    {
        $properties = $this->updateDefaultParameterRouteToUrl($properties, 'pgtUrl');

        return $this->updateDefaultParameterRouteToUrl($properties, 'service');
    }

    /**
     * @param array<string, mixed> $properties
     *
     * @return array<string, mixed>
     */
    private function updateDefaultParameterRouteToUrl(array $properties, string $key): array
    {
        foreach ($properties['protocol'] as $protocolKey => $protocol) {
            if (!isset($protocol['default_parameters'][$key])) {
                continue;
            }

            $route = $protocol['default_parameters'][$key];

            if (false === filter_var($route, FILTER_VALIDATE_URL)) {
                try {
                    $route = route($route, [], true);
                } catch (\Exception) {
                    // Keep the configured value if the route cannot be resolved.
                }

                $properties['protocol'][$protocolKey]['default_parameters'][$key] = $route;
            }
        }

        return $properties;
    }
}
