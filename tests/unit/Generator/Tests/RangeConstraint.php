<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Tests;

use PHPUnit\Framework\Constraint\Constraint;

class RangeConstraint extends Constraint
{
    /**
     * @var int
     */
    private $from;
    /**
     * @var int
     */
    private $to;

    public function __construct(int $from, int $to)
    {
        parent::__construct();
        $this->from = $from;
        $this->to = $to;
    }

    /**
     * @inheritdoc
     */
    public function toString(): string
    {
        return 'in range';
    }

    /**
     * @inheritdoc
     */
    protected function matches($other): bool
    {
        return $other >= $this->from && $other <= $this->to;
    }

    /**
     * @inheritdoc
     */
    protected function failureDescription($other): string
    {
        return \sprintf(
            '%s is not in range between %s and %s',
            $this->exporter->shortenedExport($other),
            $this->from,
            $this->to
        );
    }
}
