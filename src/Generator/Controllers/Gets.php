<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Controllers;

class Gets extends AbstractAction
{
    protected $template = 'Gets.php.twig';

    public function getClassName(): string
    {
        return $this->provider->getClassName('gets');
    }
}
