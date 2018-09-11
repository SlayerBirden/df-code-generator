<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\DataProvider;

class DecoratedProvider implements DataProviderInterface
{
    /**
     * @var DataProviderDecoratorInterface[]
     */
    private $decorators;
    /**
     * @var DataProviderInterface
     */
    private $dataProvider;

    public function __construct(DataProviderInterface $dataProvider, DataProviderDecoratorInterface ...$decorators)
    {
        $this->decorators = $decorators;
        $this->dataProvider = $dataProvider;
    }

    public function provide(): array
    {
        $data = $this->dataProvider->provide();

        foreach ($this->decorators as $decorator) {
            $data = $decorator->decorate($data);
        }

        return $data;
    }
}
