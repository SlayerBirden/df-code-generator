<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Tests;

interface IdHandlerInterface
{
    public function generateStringId(string $entity): string;

    public function generateIntId(string $entity, bool $increment): int;

    /**
     * @param string $entity
     * @return mixed
     */
    public function getId(string $entity);
}
