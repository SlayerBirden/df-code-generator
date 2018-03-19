<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Tests;

class ProviderValuePromise
{
    /**
     * @var EntityProviderInterface
     */
    private $provider;
    /**
     * @var string
     */
    private $key;

    public function __construct(EntityProviderInterface $provider, string $key)
    {
        $this->provider = $provider;
        $this->key = $key;
    }

    /**
     * Return whatever was provided by the provider
     *
     * @return mixed
     */
    public function resolve()
    {
        $params = $this->provider->getParams();
        return $params[$this->key] ?? null;
    }
}
