<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Config;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PhpLiteral;
use Nette\PhpGenerator\PhpNamespace;
use Nette\PhpGenerator\PsrPrinter;
use SlayerBirden\DFCodeGeneration\Code\Printer\NsArrayPrinter;
use SlayerBirden\DFCodeGeneration\Generator\DataProvider\DataProviderInterface;
use SlayerBirden\DFCodeGeneration\Generator\GeneratorInterface;

class ConfigGenerator implements GeneratorInterface
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
     * @var array
     */
    private $currentConfig;

    public function __construct(DataProviderInterface $dataProvider, ConfigPartInterface ...$parts)
    {
        $this->dataProvider = $dataProvider;
        $this->parts = $parts;
    }

    public function generate(): string
    {
        $invoke = [];

        $file = new PhpFile();
        $file->setStrictTypes();
        $namespace = $file->addNamespace($this->getConfigNamespace());
        $class = $namespace->addClass('ConfigProvider');

        $this->traverseParts($this->parts, $invoke, $namespace, $class, $this->getCurrentConfig());

        $this->addUses($invoke, $namespace);
        $invokeBody = 'return ' . (new NsArrayPrinter($namespace))->printArray($invoke, 1, '') . ";\n";

        $class->addMethod('__invoke')
            ->setVisibility(ClassType::VISIBILITY_PUBLIC)
            ->setReturnType('array')
            ->setBody($invokeBody);

        return (new PsrPrinter())->printFile($file);
    }

    /**
     * Iterate config parts and add methods / append main __invoke array
     *
     * @param ConfigPartInterface[] $parts
     * @param array $invoke
     * @param PhpNamespace $namespace
     * @param ClassType $class
     * @param array $currentConfig
     * @param int $indentLevel
     */
    public function traverseParts(
        array $parts,
        array &$invoke,
        PhpNamespace $namespace,
        ClassType $class,
        array $currentConfig,
        int $indentLevel = 1
    ): void {
        foreach ($parts as $part) {
            if ($part instanceof ArrayConfigPartInterface) {
                $localBody = [];
                $localCode = $part->getCode();
                $currentLocalConfig = $currentConfig[$localCode] ?? [];
                $this->traverseParts($part->getParts(), $localBody, $namespace, $class, $currentLocalConfig);
                $invoke[$localCode] = new PhpLiteral(
                    (new NsArrayPrinter($namespace))->printArray($localBody, $indentLevel + 1, "", null, false)
                );
            } else {
                $code = $part->getCode();
                $partConfig = $part->getConfig($currentConfig[$code] ?? []);
                $invoke[$code] = new PhpLiteral(sprintf('$this->%s()', $part->getMethodName()));
                $this->addUses($partConfig, $namespace);
                $class->addMethod($part->getMethodName())
                    ->setVisibility(ClassType::VISIBILITY_PUBLIC)
                    ->setBody(
                        sprintf('return %s;', (new NsArrayPrinter($namespace))->printArray($partConfig, 1, ''))
                    );
            }
        }
    }

    public function getClassName(): string
    {
        return $this->getConfigNamespace() . '\\ConfigProvider';
    }

    private function getConfigNamespace(): string
    {
        return $this->dataProvider->provide()['config_namespace'];
    }

    private function getCurrentConfig(): array
    {
        if ($this->currentConfig === null) {
            $config = $this->getClassName();

            if (class_exists($config)) {
                $this->currentConfig = (new $config())();
            }

            $this->currentConfig = [];
        }

        return $this->currentConfig;
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
        if (is_string($record) && (strpos($record, '\\') !== false)) {
            $phpNamespace->addUse($record);
        }
        if (is_string($record) && (strpos($record, '::class') !== false)) {
            $phpNamespace->addUse(str_replace('::class', '', $record));
        }
        if (($record instanceof PhpLiteral) && (strpos((string)$record, '::class') !== false)) {
            $phpNamespace->addUse(str_replace('::class', '', (string)$record));
        }
    }

    public function getFileName(): string
    {
        return sprintf('src/%s/ConfigProvider.php', $this->dataProvider->provide()['entityClassName']);
    }
}
