<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Tests;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;

class BeforeTest extends TestCase
{
    /**
     * @var ObjectProphecy[]
     */
    private $providers = [];

    protected function setUp()
    {
        $provider1 = $this->getUserProvider(1, [
            [
                'name' => 'id',
                'type' => 'integer',
                'nullable' => false,
            ],
            [
                'name' => 'name',
                'type' => 'string',
                'nullable' => false,
            ],
            [
                'name' => 'email',
                'type' => 'string',
                'nullable' => false,
            ],
            [
                'name' => 'group',
                'type' => 'manytoone',
                'nullable' => false,
                'reference' => [
                    'entity' => 'Dummy\\Group',
                ]
            ],
        ], [
            'name' => 'Bob',
            'email' => 'bob@example.com',
            'group' => 1,
        ], [
            'id' => 1,
            'name' => 'Bob',
            'email' => 'bob@example.com',
        ]);

        $provider2 = $this->getGroupProvider(1, [
            [
                'name' => 'id',
                'type' => 'integer',
                'nullable' => false,
            ],
            [
                'name' => 'name',
                'type' => 'string',
                'nullable' => false,
            ],
        ], [
            'name' => 'Default',
        ], [
            'id' => 1,
            'name' => 'Default',
        ]);

        $this->providers = [$provider1, $provider2];
    }

    private function getUserProvider(int $id, array $spec, array $post, array $params)
    {
        $provider = $this->prophesize(EntityProviderInterface::class);
        $provider->getId()->willReturn($id);
        $provider->getEntitySpec()->willReturn($spec);
        $provider->getPostParams()->willReturn($post);
        $provider->getParams()->willReturn($params);
        $provider->getBaseName()->willReturn('User');
        $provider->getShortName()->willReturn('user');
        $provider->getEntityClassName()->willReturn('Dummy\\User');
        $provider->hasUnique()->willReturn(true);
        $provider->getIdName()->willReturn('id');

        return $provider;
    }

    private function getGroupProvider(int $id, array $spec, array $post, array $params)
    {
        $provider = $this->prophesize(EntityProviderInterface::class);
        $provider->getId()->willReturn($id);
        $provider->getEntitySpec()->willReturn($spec);
        $provider->getPostParams()->willReturn($post);
        $provider->getParams()->willReturn($params);
        $provider->getBaseName()->willReturn('Group');
        $provider->getShortName()->willReturn('group');
        $provider->getEntityClassName()->willReturn('Dummy\\Group');
        $provider->hasUnique()->willReturn(false);
        $provider->getIdName()->willReturn('id');

        return $provider;
    }

    /**
     * @return EntityProviderFactoryInterface
     */
    private function getFactory()
    {
        return new class($this->providers) implements EntityProviderFactoryInterface
        {
            private $providers;

            public function __construct($providers)
            {
                $this->providers = $providers;
            }

            public function create(string $entityClassName): EntityProviderInterface
            {
                foreach ($this->providers as $id => $provider) {
                    /** @var EntityProviderInterface $entityProvider */
                    $entityProvider = $provider->reveal();
                    if ($entityProvider->getEntityClassName() == $entityClassName) {
                        unset($this->providers[$id]);
                        return $entityProvider;
                    }
                }

                throw new \LogicException('Could not create provider.');
            }
        };
    }

    public function testManyToOne()
    {
        $expected = <<<'BODY'
$I->haveInRepository('Dummy\Group', array (
  'id' => 1,
  'name' => 'Default',
));
$group = $I->grabEntityFromRepository('Dummy\Group', ['id' => 1]);
$I->haveInRepository('Dummy\User', array (
  'id' => 1,
  'name' => 'Bob',
  'email' => 'bob@example.com',
  'group' => $group,
));

BODY;

        $actual = (new class('Dummy\\User', $this->getFactory()) extends AbstractTest
        {

            public function generate(): string
            {
                return $this->generateHaveInRepo();
            }

            public function getClassName(): string
            {
                return 'TestCest';
            }
        })->generate();

        $this->assertEquals($expected, $actual);
    }

    public function testManyToOneMultiple()
    {
        $provider = $this->getUserProvider(2, [
            [
                'name' => 'id',
                'type' => 'integer',
                'nullable' => false,
            ],
            [
                'name' => 'name',
                'type' => 'string',
                'nullable' => false,
            ],
            [
                'name' => 'email',
                'type' => 'string',
                'nullable' => false,
            ],
            [
                'name' => 'group',
                'type' => 'manytoone',
                'reference' => [
                    'entity' => 'Dummy\\Group',
                ],
                'nullable' => false,
            ],
        ], [
            'name' => 'Joe',
            'email' => 'jdoe@example.com',
            'group' => 1,
        ], [
            'id' => 2,
            'name' => 'Joe',
            'email' => 'jdoe@example.com',
        ]);
        $this->providers[] = $provider;

        $expected = <<<'BODY'
$I->haveInRepository('Dummy\Group', array (
  'id' => 1,
  'name' => 'Default',
));
$group = $I->grabEntityFromRepository('Dummy\Group', ['id' => 1]);
$I->haveInRepository('Dummy\User', array (
  'id' => 1,
  'name' => 'Bob',
  'email' => 'bob@example.com',
  'group' => $group,
));
$I->haveInRepository('Dummy\User', array (
  'id' => 2,
  'name' => 'Joe',
  'email' => 'jdoe@example.com',
  'group' => $group,
));

BODY;

        $actual = (new class('Dummy\\User', $this->getFactory(), 2) extends AbstractTest
        {
            /**
             * @var int
             */
            private $times;

            public function __construct(
                string $entityClassName,
                EntityProviderFactoryInterface $entityProviderFactory,
                int $times
            ) {
                parent::__construct($entityClassName, $entityProviderFactory);
                $this->times = $times;
            }

            public function generate(): string
            {
                return $this->generateHaveInRepo($this->times);
            }

            public function getClassName(): string
            {
                return 'TestCest';
            }
        })->generate();

        $this->assertEquals($expected, $actual);
    }

    public function testManyToManyMultiple()
    {
        $spec = [
            [
                'name' => 'id',
                'type' => 'integer',
                'nullable' => false,
            ],
            [
                'name' => 'name',
                'type' => 'string',
                'nullable' => false,
            ],
            [
                'name' => 'email',
                'type' => 'string',
                'nullable' => false,
            ],
            [
                'name' => 'groups',
                'type' => 'manytomany',
                'reference' => [
                    'entity' => 'Dummy\\Group',
                ],
                'nullable' => false,
            ],
        ];
        $provider1 = $this->providers[0];
        $provider1->getEntitySpec()->willReturn($spec);
        $provider1->getPostParams()->willReturn([
            'name' => 'Bob',
            'email' => 'bob@example.com',
            'groups' => [1],
        ]);

        $provider3 = $this->getUserProvider(2, $spec, [
            'name' => 'Joe',
            'email' => 'jdoe@example.com',
            'groups' => "1,2",
        ], [
            'id' => 2,
            'name' => 'Joe',
            'email' => 'jdoe@example.com',
        ]);
        $this->providers[] = $provider3;

        $provider4 = $this->getGroupProvider(2, [
            [
                'name' => 'id',
                'type' => 'integer',
                'nullable' => false,
            ],
            [
                'name' => 'name',
                'type' => 'string',
                'nullable' => false,
            ],
        ], [
            'name' => 'Special',
        ], [
            'id' => 2,
            'name' => 'Special',
        ]);
        $this->providers[] = $provider4;

        $actual = (new class('Dummy\\User', $this->getFactory(), 2) extends AbstractTest
        {
            /**
             * @var int
             */
            private $times;

            public function __construct(
                string $entityClassName,
                EntityProviderFactoryInterface $entityProviderFactory,
                int $times
            ) {
                parent::__construct($entityClassName, $entityProviderFactory);
                $this->times = $times;
            }

            public function generate(): string
            {
                return $this->generateHaveInRepo($this->times);
            }

            public function getClassName(): string
            {
                return 'TestCest';
            }
        })->generate();

        $expected = <<<'BODY'
$I->haveInRepository('Dummy\Group', array (
  'id' => 1,
  'name' => 'Default',
));
$group = $I->grabEntityFromRepository('Dummy\Group', ['id' => 1]);
$I->haveInRepository('Dummy\User', array (
  'id' => 1,
  'name' => 'Bob',
  'email' => 'bob@example.com',
  'groups' => [$group],
));
$I->haveInRepository('Dummy\User', array (
  'id' => 2,
  'name' => 'Joe',
  'email' => 'jdoe@example.com',
  'groups' => [$group],
));

BODY;
        $this->assertEquals($expected, $actual);
    }

    public function testRelationsNullable()
    {
        $spec = [
            [
                'name' => 'id',
                'type' => 'integer',
                'nullable' => false,
            ],
            [
                'name' => 'name',
                'type' => 'string',
                'nullable' => false,
            ],
            [
                'name' => 'email',
                'type' => 'string',
                'nullable' => false,
            ],
            [
                'name' => 'group',
                'type' => 'manytoone',
                'reference' => [
                    'entity' => 'Dummy\\Group',
                ],
                'nullable' => true,
            ],
        ];
        $provider1 = $this->providers[0];
        $provider1->getEntitySpec()->willReturn($spec);

        $actual = (new class('Dummy\\User', $this->getFactory(), .0) extends AbstractTest
        {

            public function generate(): string
            {
                return $this->generateHaveInRepo();
            }

            public function getClassName(): string
            {
                return 'TestCest';
            }
        })->generate();

        $expected = <<<'BODY'
$I->haveInRepository('Dummy\User', array (
  'id' => 1,
  'name' => 'Bob',
  'email' => 'bob@example.com',
));

BODY;
        $this->assertEquals($expected, $actual);
    }

    public function testNullableColumn()
    {
        $spec = [
            [
                'name' => 'id',
                'type' => 'integer',
                'nullable' => false,
            ],
            [
                'name' => 'name',
                'type' => 'string',
                'nullable' => true,
            ],
            [
                'name' => 'email',
                'type' => 'string',
                'nullable' => false,
            ],
            [
                'name' => 'group',
                'type' => 'manytoone',
                'reference' => [
                    'entity' => 'Dummy\\Group',
                ],
                'nullable' => true,
            ],
        ];
        $provider1 = $this->providers[0];
        $provider1->getEntitySpec()->willReturn($spec);

        $actual = (new class('Dummy\\User', $this->getFactory(), .0) extends AbstractTest
        {

            public function generate(): string
            {
                return $this->generateHaveInRepo();
            }

            public function getClassName(): string
            {
                return 'TestCest';
            }
        })->generate();

        $expected = <<<'BODY'
$I->haveInRepository('Dummy\User', array (
  'id' => 1,
  'email' => 'bob@example.com',
));

BODY;
        $this->assertEquals($expected, $actual);
    }
}
