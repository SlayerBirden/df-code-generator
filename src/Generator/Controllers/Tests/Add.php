<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Controllers\Tests;

use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\FileGenerator;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\ParameterGenerator;

class Add extends AbstractTest
{
    public function generate(): string
    {
        $className = 'Add' . $this->getBaseName($this->entityClassName) . 'Cest';
        $baseName = $this->getBaseName($this->entityClassName);
        $class = new ClassGenerator($className);

        $class->addMethodFromGenerator(
            (new MethodGenerator('_before'))
                ->setParameter((new ParameterGenerator('I'))->setType('\ApiTester'))
                ->setBody($this->getBefore())
        );

        $class->addMethodFromGenerator(
            (new MethodGenerator('add' . $baseName))
                ->setParameter((new ParameterGenerator('I'))->setType('\ApiTester'))
                ->setBody($this->getSuccessCase())
        );

        $class->addMethodFromGenerator(
            (new MethodGenerator('addInvalid' . $baseName))
                ->setParameter((new ParameterGenerator('I'))->setType('\ApiTester'))
                ->setBody($this->getValidationCase())
        );

        $class->addMethodFromGenerator(
            (new MethodGenerator('addFailedConstraint' . $baseName))
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


        return sprintf($body, $this->shortName, var_export($this->getPostParams(), true));
    }

    private function getValidationCase(): string
    {
        $params = $this->getPostParams();
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
            if (is_object($param)) {
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

        return sprintf($body, $this->shortName, var_export($params, true));
    }
}
