<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\DataProvider;

final class CachedProvider implements DataProviderInterface
{
    /**
     * @var
     */
    private $cachedData;
    /**
     * @var DataProviderInterface
     */
    private $provider;

    public function __construct(DataProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    public function provide(): array
    {
        if ($this->cachedData === null) {
            $this->cachedData = $this->provider->provide();
        }

        return $this->cachedData;
    }
}
