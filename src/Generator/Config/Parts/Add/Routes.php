<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Config\Parts\Add;

use SlayerBirden\DFCodeGeneration\Generator\Config\ConfigPartInterface;
use SlayerBirden\DFCodeGeneration\Generator\DataProvider\DataProviderInterface;

final class Routes implements ConfigPartInterface
{
    const PART_KEY = 'rotes';
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
    public function getConfig(array $current = []): array
    {
        return array_merge_recursive($current, [
            [
                'path' => '/config',
                'middleware' => [
                    '\SlayerBirden\DataFlowServer\Authentication\Middleware\TokenMiddleware::class',
                    \Zend\Expressive\Helper\BodyParams\BodyParamsMiddleware::class,
                    '\SlayerBirden\DataFlowServer\Domain\Middleware\SetOwnerMiddleware::class',
                    $this->getControllerFullName(),
                ],
                'name' => 'add_config',
                'allowed_methods' => ['POST'],
            ],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getCode(): string
    {
        return self::PART_KEY;
    }

    /**
     * @inheritdoc
     */
    public function getMethodName(): string
    {
        return 'getRoutesConfig';
    }

    private function getControllerFullName(): string
    {
        return $this->dataProvider->provide()['controller_namespace'] .
            '\\' . 'Add' . $this->dataProvider->provide()['entityClassName'] . 'Action::class';
    }
}
