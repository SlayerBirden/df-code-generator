<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Tests;

use SlayerBirden\DFCodeGeneration\Generator\GeneratorInterface;

abstract class AbstractTest implements GeneratorInterface
{
    /**
     * @var EntityProviderFactoryInterface
     */
    protected $entityProviderFactory;
    /**
     * @var string
     */
    protected $entityClassName;
    /**
     * @var EntityProviderInterface[]
     */
    protected $providers = [];
    private $innerProviders = [];
    private $appended = [];
    /**
     * The default probability of Nulled values being filled with data
     * @var float
     */
    private $probabilityOfFilledNullable;

    public function __construct(
        string $entityClassName,
        EntityProviderFactoryInterface $entityProviderFactory,
        float $probabilityOfFilledNullable = .5
    ) {
        $this->entityProviderFactory = $entityProviderFactory;
        $this->entityClassName = $entityClassName;
        $this->probabilityOfFilledNullable = $probabilityOfFilledNullable;
    }

    protected function getLatestProvider()
    {
        if (count($this->providers)) {
            return end($this->providers);
        } else {
            return $this->entityProviderFactory->create($this->entityClassName);
        }
    }

    protected function generateHaveInRepo(int $count = 1): string
    {
        $generated = 0;
        $body = '';
        while ($generated < $count) {
            $provider = $this->entityProviderFactory->create($this->entityClassName);
            $body .= $this->getHaveInRepoPhrase($provider);
            $this->providers[] = $provider;
            $generated++;
        }
        return $body;
    }

    private function getInnerProvider(string $entity): EntityProviderInterface
    {
        if (!isset($this->innerProviders[$entity])) {
            $this->innerProviders[$entity] = $this->entityProviderFactory->create($entity);
        }

        return $this->innerProviders[$entity];
    }

    private function appendOnce(string $content, string $to)
    {
        if (in_array($content, $this->appended, true)) {
            return $to;
        }
        $this->appended[] = $content;
        return $to . $content;
    }

    private function getHaveInRepoPhrase(EntityProviderInterface $provider): string
    {
        $body = '';

        $params = [];
        foreach ($provider->getEntitySpec() as $item) {
            $key = $item['name'];
            $type = $item['type'];
            $reference = $item['reference'] ?? [];
            $nullable = $item['nullable'] ?? false;
            // Skip 50% of nullable values (by default)
            if ($nullable && rand(0,100)/100 > $this->probabilityOfFilledNullable) {
                continue;
            }
            if ($reference) {
                $referenceColumn = $reference['ref_column_key'] ?? 'id';
                $entity = $reference['entity'];

                switch ($type) {
                    case 'manytoone':
                        $innerProvider = $this->getInnerProvider($entity);
                        $body = $this->appendOnce(
                            $this->getHaveInRepoPhrase($innerProvider) . $this->getUsagePhrase($innerProvider, $referenceColumn),
                            $body
                        );
                        $params[$key] = '$' . $innerProvider->getShortName();
                        break;
                    case 'manytomany':
                        $innerProvider = $this->getInnerProvider($entity);
                        $body = $this->appendOnce(
                            $this->getHaveInRepoPhrase($innerProvider) . $this->getUsagePhrase($innerProvider, $referenceColumn),
                            $body
                        );
                        $params[$key] = '[$' . $innerProvider->getShortName() . ']';
                        break;
                    case 'onetoone':
                        $innerProvider = $this->entityProviderFactory->create($entity);
                        $body .= $this->getHaveInRepoPhrase($innerProvider) . $this->getUsagePhrase($innerProvider, $referenceColumn);
                        $params[$key] = '$' . $innerProvider->getShortName();
                        break;
                }
            } else {
                $params[$key] = new ProviderValuePromise($provider, $key);
            }
        }

        $params = $this->resolvePromises($params);

        $body .= '$I->haveInRepository(%1$s, %2$s);';
        $args = [
            '\'' . $provider->getEntityClassName() . '\'',
            var_export($params, true),
        ];
        $body = sprintf($body, ...$args);
        $body = preg_replace("/'(\\[?\\$\w*\\]?)'/", '$1', $body);

        return $body . PHP_EOL;
    }

    private function resolvePromises(array $params): array
    {
        return array_map(function ($item) {
            if ($item instanceof ProviderValuePromise) {
                return $item->resolve();
            }
            return $item;
        }, $params);
    }

    protected function getHaveInRepoParams(int $idx = 0): array
    {
        if (isset($this->providers[$idx])) {
            return $this->providers[$idx]->getPostParams();
        }

        throw new \InvalidArgumentException('Wrong id provided.');
    }

    protected function getUsagePhrase(EntityProviderInterface $provider, string $referenceColumn): string
    {
        $body = '$%s = $I->grabEntityFromRepository(%s, [\'' . $referenceColumn . '\' => %d]);';

        $args = [
            $provider->getShortName(),
            '\'' . $provider->getEntityClassName() . '\'',
            $provider->getId(),
        ];
        $body = sprintf($body, ...$args);

        return $body . PHP_EOL;
    }
}
