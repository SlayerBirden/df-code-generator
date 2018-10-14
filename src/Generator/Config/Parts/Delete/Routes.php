<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Config\Parts\Delete;

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
        $entity = $this->dataProvider->provide()['refName'];
        $middleware = [
            '\SlayerBirden\DataFlowServer\Authentication\Middleware\TokenMiddleware::class',
            $this->dataProvider->provide()['entityClassName'] . 'ResourceMiddleware',
        ];
        if ($this->dataProvider->provide()['has_owner']) {
            $middleware[] = '\SlayerBirden\DataFlowServer\Domain\Middleware\ValidateOwnerMiddleware::class';
        }
        $middleware[] = $this->getControllerFullName();
        return [
            [
                'path' => '/' . $entity . '/{id:\d+}',
                'middleware' => $middleware,
                'name' => 'delete_' . $entity,
                'allowed_methods' => ['DELETE'],
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
            '\\' . 'Delete' . $this->dataProvider->provide()['entityClassName'] . 'Action::class';
    }
}
