<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Controllers\Add;

abstract class AbstractUniqueFieldsAction extends AbstractAction
{
    /**
     * @var bool
     */
    protected $hasUnique;
    /**
     * @var string[]
     */
    protected $uniqueFields;

    public function __construct(string $entityClassName, bool $hasUnique, string ...$uniqueFields)
    {
        $this->hasUnique = $hasUnique;
        $this->uniqueFields = $uniqueFields;
        parent::__construct($entityClassName);
    }

    protected function getParams(): array
    {
        $params = parent::getParams();

        $message = '';
        if ($this->hasUnique) {
            $message = sprintf(
                'Provided %s already exist%s.',
                implode(' or ', $this->uniqueFields),
                count($this->uniqueFields) > 1 ? 's' : ''
            );
        }

        $params['hasUnique'] = $this->hasUnique;
        $params['uniqueIdxMessage'] = $message;

        return $params;
    }
}
