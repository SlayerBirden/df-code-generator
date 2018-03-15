<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Controllers;

class Update extends AbstractAction
{
    protected $template = 'Update.php.twig';

    public function getClassName(): string
    {
        return $this->provider->getClassName('update');
    }
}
