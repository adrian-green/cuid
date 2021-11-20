<?php

use AdrianGreen\Cuid;
use PHPUnit\Framework\TestCase as TestCase;
class ChildCuid extends Cuid
{
    public static function getFingerprint($blocksize)
    {
        return self::fingerprint($blocksize);
    }
}
class CuidTest extends TestCase
{
    const MAX_ITERATION    = 100000;
    const MIN_ITERATION    = 12000;

    public function testInvokeMagicMethod()
    {
        $cuid = new Cuid;

        $hash = $cuid();

        $this->assertIsString($hash);
        $this->assertMatchesRegularExpression(Cuid::REGEX_CUID, $hash);
    }

    public function testCuidMethod()
    {
        $cuid = new Cuid;

        $hash = $cuid::cuid();

        $this->assertIsString($hash);
        $this->assertMatchesRegularExpression(Cuid::REGEX_CUID, $hash);
    }

    public function testCuidStaticMethod()
    {
        $hash = Cuid::cuid();

        $this->assertIsString($hash);
        $this->assertMatchesRegularExpression(Cuid::REGEX_CUID, $hash);
    }

    public function testMakeMethod()
    {
        $cuid = new Cuid;

        $hash = $cuid::make();

        $this->assertIsString($hash);
        $this->assertMatchesRegularExpression(Cuid::REGEX_CUID, $hash);
    }

    public function testMakeStaticMethod()
    {
        $hash = Cuid::make();

        $this->assertIsString($hash);
        $this->assertMatchesRegularExpression(Cuid::REGEX_CUID, $hash);
    }

    public function testSlugMethod()
    {
        $cuid = new Cuid;

        $hash = $cuid::slug();

        $this->assertIsString($hash);
        $this->assertMatchesRegularExpression(Cuid::REGEX_SHORT_CUID, $hash);
    }

    public function testSlugStaticMethod()
    {
        $hash = Cuid::slug();

        $this->assertIsString($hash);
        $this->assertMatchesRegularExpression(Cuid::REGEX_SHORT_CUID, $hash);
    }

    public function testCuidUniqueness()
    {
        $ids = [];

        for ($i = 1; $i <= static::MAX_ITERATION; $i++) {
            $hash = Cuid::cuid();

            $this->assertFalse(isset($ids[$hash]));

            $ids[$hash] = $i;
        }
    }

    public function testSlugUniqueness()
    {
        $ids = [];

        for ($i = 1; $i <= static::MIN_ITERATION; $i++) {
            $hash = Cuid::slug();

            $this->assertFalse(isset($ids[$hash]));

            $ids[$hash] = $i;
        }
    }
    
    public function testIsCuidMethod()
    {
        $this->assertTrue(Cuid::isCuid(Cuid::cuid()));
    }

    public function testProtectedFingerprintResultIsStatic()
    {
        ChildCuid::init();
        foreach ([Cuid::SMALL_BLOCK, Cuid::NORMAL_BLOCK] as $blocksize) {
            $this->assertEquals(ChildCuid::getFingerprint($blocksize), ChildCuid::getFingerprint($blocksize));
        }
    }
}
