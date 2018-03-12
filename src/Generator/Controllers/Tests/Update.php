<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Controllers\Tests;

use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\FileGenerator;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\ParameterGenerator;

class Update extends AbstractTest
{
    public function generate(): string
    {
        $className = 'Update' . $this->getBaseName($this->entityClassName) . 'Cest';
        $baseName = $this->getBaseName($this->entityClassName);
        $class = new ClassGenerator($className);

        $class->addMethodFromGenerator(
            (new MethodGenerator('_before'))
                ->setParameter((new ParameterGenerator('I'))->setType('\ApiTester'))
                ->setBody($this->getBefore(2))
        );

        $class->addMethodFromGenerator(
            (new MethodGenerator('update' . $baseName))
                ->setParameter((new ParameterGenerator('I'))->setType('\ApiTester'))
                ->setBody($this->getSuccessCase())
        );

        $class->addMethodFromGenerator(
            (new MethodGenerator('updateNonExisting' . $baseName))
                ->setParameter((new ParameterGenerator('I'))->setType('\ApiTester'))
                ->setBody($this->getNonExistingCase())
        );

        $class->addMethodFromGenerator(
            (new MethodGenerator('updateSetId' . $baseName))
                ->setParameter((new ParameterGenerator('I'))->setType('\ApiTester'))
                ->setBody($this->getUpdateSetIdCase())
        );

        $class->addMethodFromGenerator(
            (new MethodGenerator('updateInvalidInput' . $baseName))
                ->setParameter((new ParameterGenerator('I'))->setType('\ApiTester'))
                ->setBody($this->getInvalidInputCase())
        );

        $class->addMethodFromGenerator(
            (new MethodGenerator('updateFailedConstraint' . $baseName))
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


        return sprintf($body, $this->shortName, $this->getId($this->entityClassName),
            var_export($this->getPostParams(), true));
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

        return sprintf($body, $this->shortName, var_export($this->getPostParams(), true));
    }

    private function getUpdateSetIdCase(): string
    {
        $body = <<<'BODY'
$I->wantTo('update %1$s and attempt to set id');
$I->haveHttpHeader('Content-Type', 'application/json');
$I->sendPUT('/%1$s/1', %2$s);
$I->seeResponseCodeIs(HttpCode::OK);
$I->seeResponseContainsJson([
    'success' => true,
    'data' => [
        '%1$s' => %3$s
    ]
]);
BODY;
        $params = $this->getPostParams();
        $paramsWithId = $params;
        $paramsWithId['id'] = 2;

        $expected = $params;
        $expected['id'] = 1;

        return sprintf($body, $this->shortName, var_export($paramsWithId, true), var_export($expected, true));
    }

    private function getInvalidInputCase(): string
    {
        $params = $this->getPostParams();
        if (count($params) > 0) {
            $body = <<<'BODY'
$I->wantTo('update %1$s set invalid input');
$I->haveHttpHeader('Content-Type', 'application/json');
$I->sendPUT('/%1$s/1', %2$s);
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
            return sprintf($body, $this->shortName, var_export($params, true), var_export($validation, true));
        } else {
            return '//TODO add validation case';
        }
    }

    private function getUniqueConstraintCase(): string
    {
        if (empty($this->unique)) {
            return '//TODO add unique case';
        }

        $params = $this->getHaveInRepoParams($this->entityClassName);
        foreach ($params as $key => $param) {
            if ($key === 'id') {
                unset($params[$key]);
            }
            if (is_object($param)) {
                $params[$key] = $param->getId();
            }
        }

        $body = <<<'BODY'
$I->wantTo('update %1$s, fail constraint');
$I->haveHttpHeader('Content-Type', 'application/json');
$I->sendPUT('/%1$s/2', %2$s);
$I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
$I->seeResponseContainsJson([
    'success' => false,
]);
BODY;

        return sprintf($body, $this->shortName, var_export($params, true));
    }
}
