<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Controllers;

class Get extends AbstractAction
{
    protected $template = 'Get.php.twig';

    public function getClassName(): string
    {
        return $this->provider->getClassName('get');
    }
}
