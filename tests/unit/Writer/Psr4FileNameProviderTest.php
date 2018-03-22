<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Writer;

use PHPUnit\Framework\TestCase;

class Psr4FileNameProviderTest extends TestCase
{
    /**
     * @dataProvider fileContentsProvider
     *
     * @param $contents
     * @param $expectedFileName
     */
    public function testGetFileName($contents, $expectedFileName)
    {
        $provider = new Psr4FileNameProvider();

        $this->assertEquals($expectedFileName, $provider->getFileName($contents));
    }

    public function fileContentsProvider(): array
    {
        $body1 = <<<'BODY'
<?php
namespace Foo\Bar\Dummy\Controller;
class AddAction {}
BODY;
        $body2 = <<<'BODY'
<?php
namespace Foo\Bar\Dummy;
class ConfigProvider {}
BODY;
        $body3 = <<<'BODY'
<?php
namespace Foo\Bar\Baz;
class Dummy {}
BODY;
        $body4 = <<<'BODY'
<?php
class DummyCest {}
BODY;
        $body5 = <<<'BODY'
<?php
class GetDummiesCest {}
BODY;
        $body6 = <<<'BODY'
<?php
class AwesomeDummiesCest {}
BODY;
        $body7 = <<<'BODY'
<?php
class GetBusesCest {}
BODY;

        return [
            [$body1, 'src/Dummy/Controller/AddAction.php'],
            [$body2, 'src/Dummy/ConfigProvider.php'],
            [$body3, 'src/Baz/Dummy.php'],
            [$body4, 'tests/api/dummy/DummyCest.php'],
            [$body5, 'tests/api/dummy/GetDummiesCest.php'],
            [$body6, 'tests/api/awesomedummy/AwesomeDummiesCest.php'],
            [$body7, 'tests/api/bus/GetBusesCest.php'],
        ];
    }
}
