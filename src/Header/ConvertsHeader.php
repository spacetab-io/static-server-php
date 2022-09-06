<?php

declare(strict_types=1);

namespace Spacetab\Server\Header;

use InvalidArgumentException;
use Spacetab\Configuration\ConfigurationInterface;
use Spacetab\Server\Exception\HeaderException;
use Throwable;

final class ConvertsHeader implements HeaderInterface
{
    private ConfigurationInterface $conf;

    /**
     * ConvertsHeader constructor.
     *
     * @param \Spacetab\Configuration\ConfigurationInterface $conf
     */
    public function __construct(ConfigurationInterface $conf)
    {
        $this->conf = $conf;
    }

    /**
     * Converts headers declared in Yaml configuration to real.
     * Due to backward compatibility.
     * https://tools.ietf.org/html/rfc5988#section-5.5
     *
     * @return array<string, string>
     * @throws \Spacetab\Server\Exception\HeaderException
     */
    public function convert(): array
    {
        $results = [];

        foreach ($this->conf->get('server.headers') as $header => $values) {
            if (!isset(self::CONFIG_MAP[$header])) {
                throw HeaderException::invalidHeaderNameParameterPassed($header);
            }

            $item = self::CONFIG_MAP[$header];

            // Backward compatibility.
            if (!isset($values[0]['value'])) {
                try {
                    $results[$item] = implode('; ', (array) $values);
                } catch (Throwable) {
                    throw new InvalidArgumentException('Headers parse error. Check : symbol or indentation (use "" for escaping :).');
                }
            }

            // Checks new extended format for sent headers from yaml values.
            if (is_array($values) && count($values) > 0 && isset($values[0]['value'])) {
                $array = [];
                foreach ($values as $value) {
                    if (!isset($value['value'])) {
                        throw HeaderException::invalidHeaderFormat();
                    }

                    $array[] = implode('; ', (array) $value['value']);
                }

                $results[$item] = implode(', ', $array);
            }
        }

        return $results;
    }
}
