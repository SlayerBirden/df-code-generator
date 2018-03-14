<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Controllers\Add;

class Delete extends AbstractAction
{
    protected $template = 'Delete.php.twig';

    public function getClassName(): string
    {
        return $this->getNs($this->entityClassName) . '\\Delete' . $this->getBaseName($this->entityClassName) . 'Action';
    }
}
