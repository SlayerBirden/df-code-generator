<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Controllers\Add;

class Gets extends AbstractAction
{
    protected $template = 'Gets.php.twig';

    public function getClassName(): string
    {
        return $this->getNs($this->entityClassName) . '\\Get' . $this->getBaseName($this->entityClassName) . 'sAction';
    }
}
