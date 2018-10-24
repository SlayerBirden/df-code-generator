<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Config\Parts\Add;

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
            \Zend\Expressive\Helper\BodyParams\BodyParamsMiddleware::class,
            $this->dataProvider->provide()['input_filter_middleware_name'],
        ];
        if ($this->dataProvider->provide()['has_owner']) {
            $middleware[] = '\SlayerBirden\DataFlowServer\Domain\Middleware\SetOwnerMiddleware::class';
        }
        $middleware[] = $this->getControllerFullName();
        return [
            [
                'path' => '/' . $entity,
                'middleware' => $middleware,
                'name' => 'add_' . $entity,
                'allowed_methods' => ['POST'],
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
            '\\' . 'Add' . $this->dataProvider->provide()['entityClassName'] . 'Action::class';
    }
}
