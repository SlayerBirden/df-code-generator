<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Config;

use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PhpLiteral;
use Nette\PhpGenerator\PhpNamespace;
use Nette\PhpGenerator\PsrPrinter;
use SlayerBirden\DFCodeGeneration\Generator\Config\Code\CodeFeederInterface;
use SlayerBirden\DFCodeGeneration\Generator\DataProvider\DataProviderInterface;
use SlayerBirden\DFCodeGeneration\Generator\GeneratorInterface;
use SlayerBirden\DFCodeGeneration\Util\ArrayUtils;

final class ConfigGenerator implements GeneratorInterface
{
    /**
     * @var DataProviderInterface
     */
    private $dataProvider;
    /**
     * @var ConfigPartInterface[]
     */
    private $parts;
    /**
     * @var CodeFeederInterface
     */
    private $codeFeeder;
    /**
     * @var CurrentConfigProviderInterface
     */
    private $currentConfigProvider;

    public function __construct(
        DataProviderInterface $dataProvider,
        CodeFeederInterface $codeFeeder,
        CurrentConfigProviderInterface $currentConfigProvider,
        ConfigPartInterface ...$parts
    ) {
        $this->dataProvider = $dataProvider;
        $this->parts = $parts;
        $this->codeFeeder = $codeFeeder;
        $this->currentConfigProvider = $currentConfigProvider;
    }

    public function generate(): string
    {
        $invoke = [];

        $file = new PhpFile();
        $file->setStrictTypes();
        $file->addComment('This file is generated by SlayerBirden\DFCodeGeneration');
        $namespace = $file->addNamespace($this->getConfigNamespace());
        $class = $namespace->addClass('ConfigProvider');
        $class->setFinal();

        foreach ($this->parts as $configPart) {
            $invoke[$configPart->getCode()] = $configPart->getConfig();
        }

        $invoke = ArrayUtils::merge($this->currentConfigProvider->getCurrentConfig($this->getClassName()), $invoke);
        $this->currentConfigProvider->setCurrentConfig($this->getClassName(), $invoke);
        $this->addUses($invoke, $namespace);
        $this->codeFeeder->feed($invoke, $class, $namespace);

        return (new PsrPrinter())->printFile($file);
    }

    public function getClassName(): string
    {
        return $this->getConfigNamespace() . '\\ConfigProvider';
    }

    private function getConfigNamespace(): string
    {
        return $this->dataProvider->provide()['config_namespace'];
    }

    private function addUses(array $config, PhpNamespace $phpNamespace): void
    {
        foreach ($config as $key => $value) {
            $this->checkRecord($key, $phpNamespace);
            if (is_array($value)) {
                $this->addUses($value, $phpNamespace);
            } else {
                $this->checkRecord($value, $phpNamespace);
            }
        }
    }

    /**
     * @param mixed $record
     * @param PhpNamespace $phpNamespace
     */
    private function checkRecord($record, PhpNamespace $phpNamespace): void
    {
        // order is important - first check records with ::class
        // only after that records just with backslash
        if (is_string($record) && (strpos($record, '::class') !== false)) {
            $phpNamespace->addUse(str_replace('::class', '', $record));
        } elseif (is_string($record) && (strpos($record, '\\') !== false)) {
            if (strpos($record, ':\\') !== false) {
                // don't add use if this is not a class name
                return;
            }
            $phpNamespace->addUse($record);
        } elseif (($record instanceof PhpLiteral) && (strpos((string)$record, '::class') !== false)) {
            $phpNamespace->addUse(str_replace('::class', '', (string)$record));
        }
    }

    public function getFileName(): string
    {
        return sprintf('src/%s/ConfigProvider.php', $this->dataProvider->provide()['moduleName']);
    }
}
