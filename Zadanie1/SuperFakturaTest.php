<?php declare(strict_types=1);

namespace app;

use PHPUnit\Framework\TestCase;

final class SuperFakturaTest extends TestCase
{
    protected static SuperFaktura $superFaktura;

    protected static array $expectedData = array(
        "1","2","Super","4","Faktura","Super","7","8","Super","Faktura",
        "11","Super","13","14","SuperFaktura","16","17","Super","19","Faktura",
        "Super","22","23","Super","Faktura","26","Super","28","29","SuperFaktura",
        "31","32","Super","34","Faktura","Super","37","38","Super","Faktura",
        "41","Super","43","44","SuperFaktura","46","47","Super","49","Faktura",
        "Super","52","53","Super","Faktura","56","Super","58","59","SuperFaktura",
        "61","62","Super","64","Faktura","Super","67","68","Super","Faktura",
        "71","Super","73","74","SuperFaktura","76","77","Super","79","Faktura",
        "Super","82","83","Super","Faktura","86","Super","88","89","SuperFaktura",
        "91","92","Super","94","Faktura","Super","97","98","Super","Faktura"
    );

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$superFaktura = new SuperFaktura();
    }

    public function testComputeData(): void
    {
        $this->assertEquals(self::$expectedData, self::$superFaktura->computeData());
    }

    public function testComputeDataAlt(): void
    {
        $this->assertEquals(self::$expectedData, self::$superFaktura->computeDataAlt());
    }
}