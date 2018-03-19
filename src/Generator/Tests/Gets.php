<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Tests;

use Faker\Factory;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\FileGenerator;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\ParameterGenerator;

class Gets extends AbstractTest
{
    public function generate(): string
    {
        $class = new ClassGenerator($this->getClassName());

        $class->addMethodFromGenerator(
            (new MethodGenerator('_before'))
                ->setParameter((new ParameterGenerator('I'))->setType('\ApiTester'))
                ->setBody($this->generateHaveInRepo(11))
        );

        $class->addMethodFromGenerator(
            (new MethodGenerator('getAll'))
                ->setParameter((new ParameterGenerator('I'))->setType('\ApiTester'))
                ->setBody($this->getAll())
        );

        $class->addMethodFromGenerator(
            (new MethodGenerator('getSecondPage'))
                ->setParameter((new ParameterGenerator('I'))->setType('\ApiTester'))
                ->setBody($this->getSecondPage())
        );

        $class->addMethodFromGenerator(
            (new MethodGenerator('getFiltered'))
                ->setParameter((new ParameterGenerator('I'))->setType('\ApiTester'))
                ->setBody($this->getFiltered())
        );

        $class->addMethodFromGenerator(
            (new MethodGenerator('getSorted'))
                ->setParameter((new ParameterGenerator('I'))->setType('\ApiTester'))
                ->setBody($this->getSorted())
        );

        $class->addMethodFromGenerator(
            (new MethodGenerator('getNoResultsFilter'))
                ->setParameter((new ParameterGenerator('I'))->setType('\ApiTester'))
                ->setBody($this->getNoResultsFilter())
        );

        $class->addMethodFromGenerator(
            (new MethodGenerator('getInvalidFilter'))
                ->setParameter((new ParameterGenerator('I'))->setType('\ApiTester'))
                ->setBody($this->getInvalidFilter())
        );

        return (new FileGenerator())
            ->setClass($class)
            ->generate();
    }

    private function getAll(): string
    {
        $body = <<<'BODY'
$I->wantTo('get all entities');
$I->haveHttpHeader('Content-Type', 'application/json');
$I->sendGET('/%1$ss');
$I->seeResponseCodeIs(HttpCode::OK);
$I->seeResponseContainsJson([
    'success' => true,
    'data' => [
        '%1$ss' => %2$s,
        'count' => 11,
    ]
]);
BODY;

        $allParams = $this->getAllParams();

        return sprintf($body, $this->getLatestProvider()->getShortName(), var_export($allParams, true));
    }

    private function getAllParams(): array
    {
        return array_map(function (EntityProviderInterface $provider) {
            return $provider->getPostParams();
        }, $this->providers);
    }

    private function getSecondPage(): string
    {
        $body = <<<'BODY'
$I->wantTo('get second page');
$I->haveHttpHeader('Content-Type', 'application/json');
$I->sendGET('/%1$ss?p=2');
$I->seeResponseCodeIs(HttpCode::OK);
$I->seeResponseContainsJson([
    'success' => true,
    'data' => [
        '%1$ss' => %2$s,
        'count' => 11,
    ]
]);
BODY;

        $params = [
            $this->getHaveInRepoParams(10)
        ];

        return sprintf($body, $this->getLatestProvider()->getBaseName(), var_export($params, true));
    }

    private function getFiltered(): string
    {
        $body = <<<'BODY'
$I->wantTo('get filtered %1$ss');
$I->haveHttpHeader('Content-Type', 'application/json');
$I->sendGET('/%1$ss?%2$s');
$I->seeResponseCodeIs(HttpCode::OK);
$I->seeResponseContainsJson([
    'success' => true,
    'data' => [
        '%1$ss' => %3$s,
        'count' => %4$d,
    ]
]);
BODY;

        $chosen = $this->getHaveInRepoParams(5);

        $key = $this->getKey($chosen);
        if (empty($key)) {
            return '//TODO add filter case';
        }
        $value = $chosen[$key];

        $found = [];
        $all = $this->getAllParams();
        foreach ($all as $item) {
            if ($item[$key] === $value) {
                $found[] = $item;
            }
        }
        $filterString = "f[$key]=$value";

        return sprintf($body, $this->getLatestProvider()->getShortName(), $filterString, var_export($found, true), count($found));
    }

    private function getKey(array $item): string
    {
        $key = '';
        foreach ($item as $key => $value) {
            if ($key === $this->getLatestProvider()->getIdName()) {
                continue;
            }
            break;
        }

        return $key;
    }

    private function getSorted(): string
    {
        $body = <<<'BODY'
$I->wantTo('get %1$ss sorted by %2$s asc');
$I->haveHttpHeader('Content-Type', 'application/json');
$I->sendGET('/%1$ss?s[%2$s]=asc');
$I->seeResponseCodeIs(HttpCode::OK);
$I->seeResponseContainsJson([
    'success' => true,
    'data' => [
        '%1$ss' => %3$s,
        'count' => 11,
    ]
]);
BODY;
        $chosen = $this->getHaveInRepoParams(5);

        $key = $this->getKey($chosen);

        if (empty($key)) {
            return '//TODO add sorted case';
        }

        $all = $this->getAllParams();
        usort($all, function (array $a, array $b) use ($key) {
            return strcmp($a[$key], $b[$key]);
        });

        return sprintf($body, $this->getLatestProvider()->getShortName(), $key, var_export($all, true));
    }

    private function getNoResultsFilter(): string
    {
        $body = <<<'BODY'
$I->wantTo('attempt to get %1$ss filtered by non existing value');
$I->haveHttpHeader('Content-Type', 'application/json');
$I->sendGET('/%1$ss?%2$s');
$I->seeResponseCodeIs(HttpCode::NOT_FOUND);
$I->seeResponseContainsJson([
    'success' => false,
    'data' => [
        '%1$ss' => [],
        'count' => 0,
    ]
]);
BODY;

        $chosen = $this->getHaveInRepoParams(5);


        $key = $this->getKey($chosen);
        if (empty($key)) {
            return '//TODO add filter case';
        }
        $value = $this->getWrongValue($key);
        $filterString = "f[$key]=$value";

        return sprintf($body, $this->getLatestProvider()->getShortName(), $filterString);
    }

    private function getWrongValue(string $key): string
    {
        $faker = Factory::create();
        $all = $this->haveInRepoParams[$this->entityClassName] ?? [];
        $values = array_map(function (array $item) use ($key) {
            return $item[$key] ?? null;
        }, $all);
        do {
            $wrongValue = $faker->word;
        } while (in_array($wrongValue, $values, true));

        return $wrongValue;
    }

    private function getWrongKey(): string
    {
        $faker = Factory::create();
        $item = $this->getHaveInRepoParams();
        $keys = array_keys($item);
        do {
            $wrongKey = $faker->word;
        } while (in_array($wrongKey, $keys, true));

        return $wrongKey;
    }

    private function getInvalidFilter(): string
    {
        $body = <<<'BODY'
$I->wantTo('attempt to get %1$ss with wrong filters');
$I->haveHttpHeader('Content-Type', 'application/json');
$I->sendGET('/%1$ss?%2$s');
$I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
$I->seeResponseContainsJson([
    'success' => false,
    'data' => [
        '%1$ss' => [],
        'count' => 0,
    ]
]);
BODY;
        $key = $this->getWrongKey();
        $filter = "f[$key]={$this->getWrongValue($key)}";

        return sprintf($body, $this->getLatestProvider()->getShortName(), $filter);
    }

    public function getClassName(): string
    {
        return 'Get' . $this->getLatestProvider()->getBaseName() . 'sCest';
    }
}
