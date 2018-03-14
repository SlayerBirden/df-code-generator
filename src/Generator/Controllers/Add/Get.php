<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Controllers\Add;

class Get extends AbstractAction
{
    protected $template = 'Get.php.twig';

    public function getClassName(): string
    {
        return $this->getNs($this->entityClassName) . '\\Get' . $this->getBaseName($this->entityClassName) . 'Action';
    }
}
