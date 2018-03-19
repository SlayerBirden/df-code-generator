<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Tests;

interface EntityProviderInterface
{
    /**
     * Immutable
     * @return mixed
     */
    public function getId();

    /**
     * Immutable
     * @return array
     */
    public function getEntitySpec(): array;

    /**
     * Immutable
     * @return array
     */
    public function getPostParams(): array;

    /**
     * Immutable
     * @return array
     */
    public function getParams(): array;

    /**
     * Immutable
     * @return string
     */
    public function getBaseName(): string;

    /**
     * Immutable
     * @return string
     */
    public function getShortName(): string;

    /**
     * Immutable
     * @return string
     */
    public function getEntityClassName(): string;

    /**
     * Immutable
     * @return bool
     */
    public function hasUnique(): bool;

    public function getIdName(): string;
}
