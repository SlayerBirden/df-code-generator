<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Tests;

use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\FileGenerator;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\ParameterGenerator;

class Add extends AbstractTest
{
    public function generate(): string
    {
        $class = new ClassGenerator($this->getClassName());

        $class->addMethodFromGenerator(
            (new MethodGenerator('_before'))
                ->setParameter((new ParameterGenerator('I'))->setType('\ApiTester'))
                ->setBody($this->generateHaveInRepo())
        );

        $class->addMethodFromGenerator(
            (new MethodGenerator('addEntity'))
                ->setParameter((new ParameterGenerator('I'))->setType('\ApiTester'))
                ->setBody($this->getSuccessCase())
        );

        $class->addMethodFromGenerator(
            (new MethodGenerator('addInvalidEntity'))
                ->setParameter((new ParameterGenerator('I'))->setType('\ApiTester'))
                ->setBody($this->getValidationCase())
        );

        $class->addMethodFromGenerator(
            (new MethodGenerator('addFailedConstraintEntity'))
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
$I->wantTo('create %1$s');
$I->haveHttpHeader('Content-Type', 'application/json');
$I->sendPOST('/%1$s', %2$s);
$I->seeResponseCodeIs(HttpCode::OK);
$I->seeResponseContainsJson([
    'success' => true,
    'data' => [
        '%1$s' => %2$s
    ]
]);
BODY;

        $provider = $this->entityProviderFactory->create($this->entityClassName);

        return sprintf($body, $provider->getShortName(), var_export($provider->getPostParams(), true));
    }

    private function getValidationCase(): string
    {
        $provider = $this->entityProviderFactory->create($this->entityClassName);
        $params = $provider->getPostParams();
        if (count($params) > 0) {
            $body = <<<'BODY'
$I->wantTo('create incomplete %1$s');
$I->haveHttpHeader('Content-Type', 'application/json');
$I->sendPOST('/%1$s', %2$s);
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
            return sprintf($body, $provider->getShortName(), var_export($params, true), var_export($validation, true));
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
        foreach ($params as $key => $param) {
            if (is_object($param) && method_exists($param, 'getId')) {
                $params[$key] = $param->getId();
            }
        }

        $body = <<<'BODY'
$I->wantTo('create %1$s with failed constraint');
$I->haveHttpHeader('Content-Type', 'application/json');
$I->sendPOST('/%1$s', %2$s);
$I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
$I->seeResponseContainsJson([
    'success' => false,
]);
BODY;

        return sprintf($body, $this->getLatestProvider()->getShortName(), var_export($params, true));
    }

    public function getClassName(): string
    {
        return 'Add' . $this->getLatestProvider()->getBaseName() . 'Cest';
    }
}
