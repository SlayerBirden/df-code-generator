<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Tests;

use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\FileGenerator;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\ParameterGenerator;

class Get extends AbstractTest
{
    /**
     * @return string
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    public function generate(): string
    {
        $className = 'Get' . $this->getBaseName($this->entityClassName) . 'Cest';
        $baseName = $this->getBaseName($this->entityClassName);
        $class = new ClassGenerator($className);

        $class->addMethodFromGenerator(
            (new MethodGenerator('_before'))
                ->setParameter((new ParameterGenerator('I'))->setType('\ApiTester'))
                ->setBody($this->getBefore())
        );

        $class->addMethodFromGenerator(
            (new MethodGenerator('get' . $baseName))
                ->setParameter((new ParameterGenerator('I'))->setType('\ApiTester'))
                ->setBody($this->getSuccessCase())
        );

        $class->addMethodFromGenerator(
            (new MethodGenerator('getNonExisting' . $baseName))
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
$I->wantTo('get %1$s');
$I->haveHttpHeader('Content-Type', 'application/json');
$I->sendGET('/%1$s/%2$d');
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
$I->wantTo('get non-existing %1$s');
$I->haveHttpHeader('Content-Type', 'application/json');
$I->sendGET('/%1$s/0');
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
