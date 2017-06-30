<?php

namespace ParaTest\Coverage;

use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Filter;

class CoverageMergerTest extends \TestBase
{
    protected function setUp()
    {
        $this->skipIfCodeCoverageNotEnabled();
    }

    /**
     * Test merge for code coverage library 3 version.
     *
     * @requires function \PHP_CodeCoverage::merge
     */
    public function testSimpleMergeLegacy()
    {
        $firstFile = PARATEST_ROOT . '/src/Logging/LogInterpreter.php';
        $secondFile = PARATEST_ROOT . '/src/Logging/MetaProvider.php';

        $filter = new \PHP_CodeCoverage_Filter();
        $filter->addFilesToWhitelist([$firstFile, $secondFile]);
        $coverage1 = new \PHP_CodeCoverage(null, $filter);
        $coverage1->append(
            [
                $firstFile => [35 => 1],
                $secondFile => [34 => 1],
            ],
            'Test1'
        );
        $coverage2 = new \PHP_CodeCoverage(null, $filter);
        $coverage2->append(
            [
                $firstFile => [35 => 1, 36 => 1],
            ],
            'Test2'
        );

        $merger = new CoverageMerger();
        $this->call($merger, 'addCoverage', $coverage1);
        $this->call($merger, 'addCoverage', $coverage2);

        /** @var \PHP_CodeCoverage $coverage */
        $coverage = $this->getObjectValue($merger, 'coverage');

        $this->assertInstanceOf('\\PHP_CodeCoverage', $coverage);

        $data = $coverage->getData();

        $this->assertCount(2, $data[$firstFile][35]);
        $this->assertEquals('Test1', $data[$firstFile][35][0]);
        $this->assertEquals('Test2', $data[$firstFile][35][1]);

        $this->assertCount(1, $data[$firstFile][36]);
        $this->assertEquals('Test2', $data[$firstFile][36][0]);

        $this->assertCount(1, $data[$secondFile][34]);
        $this->assertEquals('Test1', $data[$secondFile][34][0]);
    }

    /**
     * Test merge for code coverage library 4 version.
     *
     * @requires function \SebastianBergmann\CodeCoverage\CodeCoverage::merge
     */
    public function testSimpleMerge()
    {
        $firstFile = PARATEST_ROOT . '/src/Logging/LogInterpreter.php';
        $secondFile = PARATEST_ROOT . '/src/Logging/MetaProvider.php';

        $firstFileFirstLine = 36;
        $secondFileFirstLine = 36;

        $filter = new Filter();
        $filter->addFilesToWhitelist([$firstFile, $secondFile]);
        $coverage1 = new CodeCoverage(null, $filter);
        $coverage1->append(
            [
                $firstFile => [$firstFileFirstLine => 1],
                $secondFile => [$secondFileFirstLine => 1],
            ],
            'Test1'
        );
        $coverage2 = new CodeCoverage(null, $filter);
        $coverage2->append(
            [
                $firstFile => [$firstFileFirstLine => 1, 1 + $firstFileFirstLine => 1],
            ],
            'Test2'
        );

        $merger = new CoverageMerger();
        $this->call($merger, 'addCoverage', $coverage1);
        $this->call($merger, 'addCoverage', $coverage2);

        /** @var CodeCoverage $coverage */
        $coverage = $this->getObjectValue($merger, 'coverage');

        $this->assertInstanceOf('\\SebastianBergmann\\CodeCoverage\\CodeCoverage', $coverage);

        $data = $coverage->getData();

        $this->assertCount(2, $data[$firstFile][$firstFileFirstLine]);
        $this->assertEquals('Test1', $data[$firstFile][$firstFileFirstLine][0]);
        $this->assertEquals('Test2', $data[$firstFile][$firstFileFirstLine][1]);

        $this->assertCount(1, $data[$firstFile][1 + $firstFileFirstLine]);
        $this->assertEquals('Test2', $data[$firstFile][1 + $firstFileFirstLine][0]);

        $this->assertCount(1, $data[$secondFile][$secondFileFirstLine]);
        $this->assertEquals('Test1', $data[$secondFile][$secondFileFirstLine][0]);
    }
}
