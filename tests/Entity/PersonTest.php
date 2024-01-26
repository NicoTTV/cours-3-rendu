<?php

namespace tests;

use App\Entity\Person;
use App\Entity\Product;
use App\Entity\Wallet;
use Faker\Generator;
use PHPUnit\Framework\TestCase;

class PersonTest extends TestCase
{

    private static Generator $faker;
    private Person $person;
    private String $name;
    private String $walletCurrency;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$faker = \Faker\Factory::create();
    }

    protected function setUp(): void
    {
        $this->name = self::$faker->name;
        $this->walletCurrency = 'USD';
        $this->person = new Person($this->name, $this->walletCurrency);
    }

    public function testConstruct()
    {
        $this->assertInstanceOf(Person::class, $this->person);
    }

    public function testGetName()
    {
        $this->assertIsString($this->person->getName());
        $this->assertEquals($this->name, $this->person->getName());
    }

    public function testSetName()
    {
        $newName = self::$faker->name;
        $this->person->setName($newName);
        $this->assertIsString($this->person->getName());
        $this->assertNotEquals($this->name, $this->person->getName());
        $this->assertEquals($newName, $this->person->getName());
    }

    public function testGetWallet()
    {
        $this->assertInstanceOf(Wallet::class, $this->person->getWallet());
        $this->assertNotNull($this->person->getWallet());
        $this->assertEquals($this->walletCurrency, $this->person->getWallet()->getCurrency());
    }

    public function testSetWallet()
    {
        $newWalletCurrency = 'EUR';
        $this->person->setWallet(new Wallet($newWalletCurrency));
        $this->assertInstanceOf(Wallet::class, $this->person->getWallet());
        $this->assertNotNull($this->person->getWallet());
        $this->assertNotEquals($this->walletCurrency, $this->person->getWallet()->getCurrency());
        $this->assertEquals($newWalletCurrency, $this->person->getWallet()->getCurrency());
    }

    public function testSetWalletWithInvalidCurrency()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid currency');
        $this->person->setWallet(new Wallet('NOT_A_CURRENCY'));
    }

    public function testSetWalletWithNull()
    {
        $this->expectException(\TypeError::class);
        $this->person->setWallet(null);
    }

    public function testHasFundWithFund()
    {
        $this->person->getWallet()->setBalance(1);
        $this->assertTrue($this->person->hasFund());
    }

    public function testHasFundWithNoFund()
    {
        $this->assertFalse($this->person->hasFund());
    }

    public function testTransferFund()
    {
        $personToTransfer = new Person(self::$faker->name, $this->walletCurrency);

        $this->person->getWallet()->setBalance(1);
        $this->assertTrue($this->person->hasFund());

        $this->person->transfertFund(1, $personToTransfer);
        $this->assertFalse($this->person->hasFund());
        $this->assertEquals(0.0, $this->person->getWallet()->getBalance(), 'The balance of the current person should be 0');
        $this->assertTrue($personToTransfer->hasFund());
        $this->assertEquals(1.0, $personToTransfer->getWallet()->getBalance(), 'The balance of the other person to transfer should be 1');

    }

    public function testTransferFundWithNoFound()
    {
        $personToTransfer = new Person(self::$faker->name, $this->walletCurrency);
        $this->assertFalse($this->person->hasFund(), 'The current person should not have fund');
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Insufficient funds');
        $this->person->transfertFund(1, $personToTransfer);

        $this->assertEquals(0, $this->person->getWallet()->getBalance(), 'The balance of the current person should not change');
        $this->assertEquals(0, $personToTransfer->getWallet()->getBalance(), 'The balance of the other person to transfer should not change');
    }

    public function testTransferFundWithoutSameCurrency()
    {
        $personToTransfer = new Person(self::$faker->name, $this->walletCurrency);
        $personToTransfer->getWallet()->setCurrency('EUR');
        $this->person->getWallet()->setBalance(1);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Can\'t give money with different currencies');
        $this->person->transfertFund(1, $personToTransfer);

        $this->assertEquals(1, $this->person->getWallet()->getBalance(), 'The balance of the current person should not change');
        $this->assertEquals(0, $personToTransfer->getWallet()->getBalance(), 'The balance of the other person to transfer should not change');
    }

    public function testDivideWallet()
    {
        $firstPersonToDivide = new Person(self::$faker->name, $this->walletCurrency);
        $secondPersonToDivide = new Person(self::$faker->name, $this->walletCurrency);
        $firstPersonToDivide->getWallet()->setBalance(0);
        $secondPersonToDivide->getWallet()->setBalance(0);
        $this->person->getWallet()->setBalance(9.0);
        $this->person->divideWallet([$firstPersonToDivide, $secondPersonToDivide]);
        $this->assertEquals(0.0, $this->person->getWallet()->getBalance(), 'The balance of the current person should be 1');
        $this->assertEquals(4.5, $firstPersonToDivide->getWallet()->getBalance(), 'The balance of the other person to transfer should be 1');
        $this->assertEquals(4.5, $secondPersonToDivide->getWallet()->getBalance(), 'The balance of the other person to transfer should be 1');
    }

    public function testDivideWalletWithDifferentCurrency()
    {
        $firstPersonToDivide = new Person(self::$faker->name, $this->walletCurrency);
        $secondPersonToDivide = new Person(self::$faker->name, $this->walletCurrency);
        $firstPersonToDivide->getWallet()->setBalance(12.0);
        $secondPersonToDivide->getWallet()->setBalance(22.0);
        $firstPersonToDivide->getWallet()->setCurrency('EUR');
        $secondPersonToDivide->getWallet()->setCurrency('EUR');
        $this->person->getWallet()->setBalance(9.0);

        $this->expectException(\DivisionByZeroError::class);
        $this->expectExceptionMessage('Division by zero');
        $this->person->divideWallet([$firstPersonToDivide, $secondPersonToDivide]);

        $this->assertEquals(9.0, $this->person->getWallet()->getBalance(), 'The balance of the current person should be 1');
        $this->assertEquals(12.0, $firstPersonToDivide->getWallet()->getBalance(), 'The balance of the other person to transfer should be 1');
        $this->assertEquals(22.0, $secondPersonToDivide->getWallet()->getBalance(), 'The balance of the other person to transfer should be 1');
    }

    public function testBuyProduct()
    {
        $product = new Product('ProductTest', ['USD' => 10], 'food');
        $this->person->getWallet()->setBalance(10);
        $this->person->buyProduct($product);
        $this->assertEquals(0, $this->person->getWallet()->getBalance(), 'The balance of the current person should be 0');
    }

    public function testBuyProductWithNoFund()
    {
        $product = new Product('ProductTest', ['USD' => 10], 'food');
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Insufficient funds');
        $this->person->buyProduct($product);
        $this->assertEquals(0, $this->person->getWallet()->getBalance(), 'The balance of the current person should be 0');
    }

    public function testBuyProductWithDifferentCurrency()
    {
        $product = new Product('ProductTest', ['EUR' => 10], 'food');
        $this->person->getWallet()->setBalance(10);
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Can\'t buy product with this wallet currency');
        $this->person->buyProduct($product);
        $this->assertEquals(10, $this->person->getWallet()->getBalance(), 'The balance of the current person should be 10');
    }
}