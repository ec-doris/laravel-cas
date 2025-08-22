<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/brownrl/laravel-cas
 */

declare(strict_types=1);

namespace EcPhp\LaravelCas\Config;

use ArrayAccess;
use EcPhp\CasLib\Configuration\Properties as PsrCasConfiguration;
use EcPhp\CasLib\Contract\Configuration\PropertiesInterface;
use Illuminate\Routing\Router;
use Illuminate\Routing\Router as RouterInterface;
use ReturnTypeWillChange;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use const FILTER_VALIDATE_URL;

final class Laravel implements PropertiesInterface, ArrayAccess
{
    private PropertiesInterface $cas;

    private RouterInterface $router;

    public function __construct(
        ParameterBag $parameterBag,
        Router $router
    ) {
        $this->router = $router;
        $this->cas = new PsrCasConfiguration(
            $this->routeToUrl(
                $parameterBag->all()
            )
        );
    }

    public function all(): array
    {
        return $this->cas->jsonSerialize();
    }

    /**
     * @param mixed $offset
     */
    #[ReturnTypeWillChange]
    public function offsetExists($offset): bool
    {
        $properties = $this->cas->jsonSerialize();
        return isset($properties[$offset]);
    }

    /**
     * @param mixed $offset
     *
     * @return array<string, mixed>|mixed
     */
    #[ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        $properties = $this->cas->jsonSerialize();
        return $properties[$offset] ?? null;
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value): void
    {
        // Note: Properties class is immutable, so this is a no-op
        // In a real implementation, you might want to store changes separately
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset): void
    {
        // Note: Properties class is immutable, so this is a no-op
        // In a real implementation, you might want to store changes separately
    }

    public function jsonSerialize(): array
    {
        return $this->cas->jsonSerialize();
    }

    /**
     * Transform Symfony routes into absolute URLs.
     *
     * @param array<string, mixed> $properties
     *                                         The properties.
     *
     * @return array<string, mixed>
     *                              The updated properties.
     */
    private function routeToUrl(array $properties): array
    {
        $properties = $this->updateDefaultParameterRouteToUrl(
            $properties,
            'pgtUrl'
        );

        return $this->updateDefaultParameterRouteToUrl(
            $properties,
            'service'
        );
    }

    /**
     * @param array<string, mixed> $properties
     *
     * @return array<string, mixed>
     */
    private function updateDefaultParameterRouteToUrl(array $properties, string $key): array
    {
        foreach ($properties['protocol'] as $protocolKey => $protocol) {
            if (false === isset($protocol['default_parameters'][$key])) {
                continue;
            }

            $route = $protocol['default_parameters'][$key];

            if (false === filter_var($route, FILTER_VALIDATE_URL)) {
                $route = $this
                    ->router
                    ->generate(
                        $route,
                        [],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    );

                $properties['protocol'][$protocolKey]['default_parameters'][$key] = $route;
            }
        }

        return $properties;
    }
}
