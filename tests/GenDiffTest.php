<?php
namespace Php\Package\Tests;

use Php\Package\Tests\Utils\FixtureLoader;
use PHPUnit\Framework\TestCase;
use function Differ\diffToStr;
use function Differ\genDiff;

class GenDiffTest extends TestCase
{
    use FixtureLoader;

    public function testSuccessLoadDiff(): void
    {
        $first = json_decode($this->getFixturesContent('file1.json'), true);
        $second = json_decode($this->getFixturesContent('file2.json'), true);

        $expected = $this->getFixturesContent('expected_diff.txt');
        $diff = genDiff($first, $second);
        $result = diffToStr($diff, 'txt');
        self::assertEquals($expected, $result);
    }
}