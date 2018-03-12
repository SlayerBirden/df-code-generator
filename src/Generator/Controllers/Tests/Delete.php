<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Controllers\Tests;

use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\FileGenerator;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\ParameterGenerator;

class Delete extends AbstractTest
{
    public function generate(): string
    {
        $className = 'Delete' . $this->getBaseName($this->entityClassName) . 'Cest';
        $baseName = $this->getBaseName($this->entityClassName);
        $class = new ClassGenerator($className);

        $class->addMethodFromGenerator(
            (new MethodGenerator('_before'))
                ->setParameter((new ParameterGenerator('I'))->setType('\ApiTester'))
                ->setBody($this->getBefore())
        );

        $class->addMethodFromGenerator(
            (new MethodGenerator('delete' . $baseName))
                ->setParameter((new ParameterGenerator('I'))->setType('\ApiTester'))
                ->setBody($this->getSuccessCase())
        );

        $class->addMethodFromGenerator(
            (new MethodGenerator('deleteNonExisting' . $baseName))
                ->setParameter((new ParameterGenerator('I'))->setType('\ApiTester'))
                ->setBody($this->getNonExistingCase())
        );

        return (new FileGenerator())
            ->setClass($class)
            ->generate();
    }

    private function getSuccessCase(): string
    {
        $body = <<<'BODY'
$I->wantTo('delete %1$s');
$I->haveHttpHeader('Content-Type', 'application/json');
$I->sendDELETE('/%1$s/%2$d');
$I->seeResponseCodeIs(HttpCode::OK);
$I->seeResponseContainsJson([
    'success' => true,
    'data' => [
        '%1$s' => %3$s
    ]
]);
BODY;

        $id = $this->getId($this->entityClassName);
        $params = $this->getHaveInRepoParams($this->entityClassName);
        if (isset($params['id'])) {
            unset($params['id']);
        }

        return sprintf($body, $this->shortName, $id, var_export($params, true));
    }

    private function getNonExistingCase(): string
    {
        $body = <<<'BODY'
$I->wantTo('delete non-existing %1$s');
$I->haveHttpHeader('Content-Type', 'application/json');
$I->sendDELETE('/%1$s/0');
$I->seeResponseCodeIs(HttpCode::NOT_FOUND);
$I->seeResponseContainsJson([
    'success' => false,
    'data' => [
        '%1$s' => null
    ]
]);
BODY;

        return sprintf($body, $this->shortName);
    }
}
