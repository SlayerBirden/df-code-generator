<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Config\Parts\Gets;

use SlayerBirden\DFCodeGeneration\Generator\Config\ConfigPartInterface;
use SlayerBirden\DFCodeGeneration\Generator\DataProvider\DataProviderInterface;

final class Routes implements ConfigPartInterface
{
    const PART_KEY = 'routes';
    /**
     * @var DataProviderInterface
     */
    private $dataProvider;

    public function __construct(DataProviderInterface $dataProvider)
    {
        $this->dataProvider = $dataProvider;
    }

    /**
     * @inheritdoc
     */
    public function getConfig(): array
    {
        $middleware = [
            '\SlayerBirden\DataFlowServer\Authentication\Middleware\TokenMiddleware::class',
        ];
        if ($this->dataProvider->provide()['has_owner']) {
            $middleware[] = '\SlayerBirden\DataFlowServer\Domain\Middleware\SetOwnerFilterMiddleware::class';
        }
        $middleware[] = $this->getControllerFullName();
        $pluralRefName = $this->dataProvider->provide()['pluralRefName'];
        return [
            [
                'path' => '/' . $pluralRefName,
                'middleware' => $middleware,
                'name' => 'get_' . $pluralRefName,
                'allowed_methods' => ['GET'],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function getCode(): string
    {
        return self::PART_KEY;
    }

    private function getControllerFullName(): string
    {
        return $this->dataProvider->provide()['controller_namespace'] .
            '\\' . 'Get' . $this->dataProvider->provide()['pluralEntityName'] . 'Action::class';
    }
}
