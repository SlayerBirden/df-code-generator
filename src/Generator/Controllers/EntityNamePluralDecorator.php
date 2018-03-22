<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Controllers;

use SlayerBirden\DFCodeGeneration\Util\Lexer;

class EntityNamePluralDecorator implements DataProviderDecoratorInterface
{
    public function decorate(array $data): array
    {
        if (isset($data['entityName'])) {
            $data['pluralEntityName'] = Lexer::getPluralForm($data['entityName']);
        }

        return $data;
    }
}
