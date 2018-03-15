<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Controllers;

class Add extends AbstractAction
{
    protected $template = 'Add.php.twig';

    public function getClassName(): string
    {
        return $this->provider->getClassName('add');
    }
}
