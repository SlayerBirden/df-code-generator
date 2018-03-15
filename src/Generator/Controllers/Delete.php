<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Controllers;

class Delete extends AbstractAction
{
    protected $template = 'Delete.php.twig';

    public function getClassName(): string
    {
        return $this->provider->getClassName('delete');
    }
}
