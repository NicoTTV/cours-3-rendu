<?php

namespace tests;

use App\Entity\Wallet;
use PHPUnit\Framework\TestCase;

class WalletTest extends TestCase
{
    private static \Faker\Generator $faker;
    private float $balance;
    private string $currency;
    private Wallet $wallet;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$faker = \Faker\Factory::create();
    }

    protected function setUp(): void
    {
        $this->balance = self::$faker->randomFloat(2, 0, 1000);
        $this->currency = self::$faker->randomElement(['USD', 'EUR']);
        $this->wallet = new Wallet($this->currency);
    }

    public function testConstruct()
    {
        $this->assertInstanceOf(Wallet::class, $this->wallet);
    }

    public function testSetAndGetBalance()
    {
        $this->wallet->setBalance($this->balance);
        $this->assertIsFloat($this->wallet->getBalance());
        $this->assertEquals($this->balance, $this->wallet->getBalance());
    }

    public function testSetBalanceThrowsException()
    {
        $this->expectException(\Exception::class);
        $this->wallet->setBalance(-1);
    }

    public function testSetCurrency()
    {
        $this->wallet->setCurrency($this->currency);
        $this->assertIsString($this->wallet->getCurrency());
        $this->assertEquals($this->currency, $this->wallet->getCurrency());
    }

    public function testGetCurrency()
    {
        $this->assertIsString($this->wallet->getCurrency());
        $this->assertEquals($this->currency, $this->wallet->getCurrency());
    }

    public function testSetCurrencyThrowsException()
    {
        $this->expectException(\Exception::class);
        $this->wallet->setCurrency('JPY');
    }

    public function testRemoveFund()
    {
        $this->wallet->setBalance(10);
        $this->wallet->removeFund(10);
        $this->assertEquals(0, $this->wallet->getBalance());
    }

    public function testRemoveFundThrowsException()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Insufficient funds');
        $this->wallet->removeFund(10);
    }

    public function testRemoveFundThrowsExceptionWithNegativeAmount()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid amount');
        $this->wallet->removeFund(-10);
    }

    public function testAddFund()
    {
        $this->wallet->addFund(10);
        $this->assertEquals(10, $this->wallet->getBalance());
    }

    public function testAddFundThrowsExceptionWithNegativeAmount()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid amount');
        $this->wallet->addFund(-10);
    }

}