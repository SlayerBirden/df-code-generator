<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Tests;

use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\FileGenerator;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\ParameterGenerator;

class Update extends AbstractTest
{
    public function generate(): string
    {
        $class = new ClassGenerator($this->getClassName());

        $class->addMethodFromGenerator(
            (new MethodGenerator('_before'))
                ->setParameter((new ParameterGenerator('I'))->setType('\ApiTester'))
                ->setBody($this->generateHaveInRepo(2))
        );

        $class->addMethodFromGenerator(
            (new MethodGenerator('updateEntity'))
                ->setParameter((new ParameterGenerator('I'))->setType('\ApiTester'))
                ->setBody($this->getSuccessCase())
        );

        $class->addMethodFromGenerator(
            (new MethodGenerator('updateNonExistingEntity'))
                ->setParameter((new ParameterGenerator('I'))->setType('\ApiTester'))
                ->setBody($this->getNonExistingCase())
        );

        $class->addMethodFromGenerator(
            (new MethodGenerator('updateSetIdEntity'))
                ->setParameter((new ParameterGenerator('I'))->setType('\ApiTester'))
                ->setBody($this->getUpdateSetIdCase())
        );

        $class->addMethodFromGenerator(
            (new MethodGenerator('updateInvalidInput'))
                ->setParameter((new ParameterGenerator('I'))->setType('\ApiTester'))
                ->setBody($this->getInvalidInputCase())
        );

        $class->addMethodFromGenerator(
            (new MethodGenerator('updateFailedConstraint'))
                ->setParameter((new ParameterGenerator('I'))->setType('\ApiTester'))
                ->setBody($this->getUniqueConstraintCase())
        );

        return (new FileGenerator())
            ->setClass($class)
            ->generate();
    }

    private function getSuccessCase(): string
    {
        $body = <<<'BODY'
$I->wantTo('update %1$s');
$I->haveHttpHeader('Content-Type', 'application/json');
$I->sendPUT('/%1$s/%2$d', %3$s);
$I->seeResponseCodeIs(HttpCode::OK);
$I->seeResponseContainsJson([
    'success' => true,
    'data' => [
        '%1$s' => %3$s
    ]
]);
BODY;

        $provider = $this->entityProviderFactory->create($this->entityClassName);

        return sprintf($body, $provider->getShortName(), $this->getLatestProvider()->getId(),
            var_export($provider->getPostParams(), true));
    }

    private function getNonExistingCase(): string
    {
        $body = <<<'BODY'
$I->wantTo('update non existing %1$s');
$I->haveHttpHeader('Content-Type', 'application/json');
$I->sendPUT('/%1$s/0', %2$s);
$I->seeResponseCodeIs(HttpCode::NOT_FOUND);
$I->seeResponseContainsJson([
    'success' => false,
    'data' => [
        '%1$s' => null
    ]
]);
BODY;

        $provider = $this->entityProviderFactory->create($this->entityClassName);

        return sprintf($body, $provider->getShortName(), var_export($provider->getPostParams(), true));
    }

    private function getUpdateSetIdCase(): string
    {
        $body = <<<'BODY'
$I->wantTo('update %1$s and attempt to set id');
$I->haveHttpHeader('Content-Type', 'application/json');
$I->sendPUT('/%1$s/%4$d', %2$s);
$I->seeResponseCodeIs(HttpCode::OK);
$I->seeResponseContainsJson([
    'success' => true,
    'data' => [
        '%1$s' => %3$s
    ]
]);
BODY;
        $first = reset($this->providers);
        $last = end($this->providers);


        $provider = $this->entityProviderFactory->create($this->entityClassName);
        $paramsWithId = $provider->getPostParams();
        $paramsWithId['id'] = $last->getId();

        $expected = $provider->getPostParams();
        $expected['id'] = $first->getId();

        return sprintf($body, $provider->getShortName(), var_export($paramsWithId, true), var_export($expected, true),
            $first->getId());
    }

    private function getInvalidInputCase(): string
    {
        $provider = $this->entityProviderFactory->create($this->entityClassName);
        $params = $provider->getPostParams();
        if (count($params) > 0) {
            $body = <<<'BODY'
$I->wantTo('update %1$s set invalid input');
$I->haveHttpHeader('Content-Type', 'application/json');
$I->sendPUT('/%1$s/%4$d', %2$s);
$I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
$I->seeResponseContainsJson([
    'success' => false,
    'data' => [
        'validation' => %3$s
    ]
]);
BODY;

            $validation = [];
            foreach ($params as $key => $val) {
                $validation[] = [
                    'field' => $key
                ];
                unset($params[$key]);
                break;
            }
            return sprintf($body, $provider->getShortName(), var_export($params, true), var_export($validation, true),
                $this->getLatestProvider()->getId());
        } else {
            return '//TODO add validation case';
        }
    }

    private function getUniqueConstraintCase(): string
    {
        if (!$this->getLatestProvider()->hasUnique()) {
            return '//TODO add unique case';
        }

        $params = $this->getHaveInRepoParams();

        $body = <<<'BODY'
$I->wantTo('update %1$s, fail constraint');
$I->haveHttpHeader('Content-Type', 'application/json');
$I->sendPUT('/%1$s/%3$d', %2$s);
$I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
$I->seeResponseContainsJson([
    'success' => false,
]);
BODY;

        return sprintf($body, $this->getLatestProvider()->getShortName(), var_export($params, true),
            $this->getLatestProvider()->getId());
    }

    public function getClassName(): string
    {
        return 'Update' . $this->getLatestProvider()->getBaseName() . 'Cest';
    }
}
