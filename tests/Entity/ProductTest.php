<?php

namespace tests;

use App\Entity\Person;
use App\Entity\Product;
use Faker\Generator;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
    private static Generator $faker;

    private string $name;
    private array $prices;
    private string $type;
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$faker = \Faker\Factory::create();
    }

    protected function setUp(): void
    {
        $this->name = self::$faker->name;
        $this->prices = [
            'USD' => self::$faker->randomFloat(2, 0, 1000),
            'EUR' => self::$faker->randomFloat(2, 0, 1000),
        ];
        $this->type = self::$faker->randomElement(['food', 'tech', 'alcohol']);
    }

    public function testConstruct()
    {
        $product = new Product($this->name, $this->prices, $this->type);
        $this->assertInstanceOf(Product::class, $product);
    }

    public function testGetName()
    {
        $product = new Product($this->name, $this->prices, $this->type);
        $this->assertIsString($product->getName());
        $this->assertEquals($this->name, $product->getName());
    }

    public function testGetPrices()
    {
        $product = new Product($this->name, $this->prices, $this->type);
        $this->assertIsArray($product->getPrices());
        $this->assertEquals($this->prices, $product->getPrices());
    }

    public function testGetType()
    {
        $product = new Product($this->name, $this->prices, $this->type);
        $this->assertIsString($product->getType());
        $this->assertEquals($this->type, $product->getType());
    }

    public function testSetName()
    {
        $product = new Product($this->name, $this->prices, $this->type);
        $newName = self::$faker->name;
        $product->setName($newName);
        $this->assertIsString($product->getName());
        $this->assertNotEquals($this->name, $product->getName());
        $this->assertEquals($newName, $product->getName());
    }

    public function testSetPrices()
    {
        $product = new Product($this->name, $this->prices, $this->type);
        $newPrices = [
            'USD' => self::$faker->randomFloat(2, 0, 1000),
            'EUR' => self::$faker->randomFloat(2, 0, 1000),
        ];
        $product->setPrices($newPrices);
        $this->assertIsArray($product->getPrices());
        $this->assertNotEquals($this->prices, $product->getPrices());
        $this->assertEquals($newPrices, $product->getPrices());
    }

    public function testSetType()
    {
        $product = new Product($this->name, $this->prices, $this->type);
        $newType = 'other';
        $product->setType($newType);
        $this->assertIsString($product->getType());
        $this->assertNotEquals($this->type, $product->getType());
        $this->assertEquals($newType, $product->getType());
    }

    public function testSetTypeWithInvalidType()
    {
        $product = new Product($this->name, $this->prices, $this->type);
        $this->expectException(\Exception::class);
        $product->setType('invalid');
    }

    public function testGetTVA()
    {
        $product = new Product($this->name, $this->prices, 'food');
        $this->assertIsFloat($product->getTVA());
        $this->assertEquals(0.1, $product->getTVA());
    }

    public function testGetTVAWithAlcohol()
    {
        $product = new Product($this->name, $this->prices, 'alcohol');
        $this->assertIsFloat($product->getTVA());
        $this->assertEquals(0.2, $product->getTVA());
    }

    public function testListCurrencies()
    {
        $product = new Product($this->name, $this->prices, $this->type);
        $this->assertIsArray($product->listCurrencies());
        $this->assertEquals(['USD', 'EUR'], $product->listCurrencies());
    }

    public function testGetPrice()
    {
        $product = new Product($this->name, $this->prices, $this->type);
        $this->assertIsFloat($product->getPrice('USD'));
        $this->assertEquals($this->prices['USD'], $product->getPrice('USD'));
    }

    public function testGetPriceWithInvalidCurrency()
    {
        $product = new Product($this->name, $this->prices, $this->type);
        $this->expectException(\Exception::class);
        $product->getPrice('invalid');
    }

}