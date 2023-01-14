<?php

declare(strict_types=1);

namespace vadimcontenthunter\MyDB\Tests;

use PHPUnit\Framework\TestCase;
use vadimcontenthunter\MyDB\Tests\Fakes\MySqlOperatorsFake;
use vadimcontenthunter\MyDB\Exceptions\QueryBuilderException;

/**
 * @author    Vadim Volkovskyi <project.k.vadim@gmail.com>
 * @copyright (c) Vadim Volkovskyi 2022
 */
class MySqlOperatorsTest extends TestCase
{
    protected MySqlOperatorsFake $mySqlOperatorsFake;

    public function setUp(): void
    {
        $this->mySqlOperatorsFake = new MySqlOperatorsFake();
    }

    /**
     * @test
     * @dataProvider providerSetQuery
     */
    public function test_setQuery_withParameters_shouldSaveTheQueryAndTheCommand(
        string $query,
        string $command
    ): void {
        $this->mySqlOperatorsFake->setQuery($query);
        $this->assertEquals($query, $this->mySqlOperatorsFake->getQueryFake());
        $this->assertEquals($command, $this->mySqlOperatorsFake->getCommandFake());
    }

    /**
     * @return array<string,mixed>
     */
    public function providerSetQuery(): array
    {
        return [
            'Command UPDATE' => [
                "UPDATE Products SET Manufacturer = 'Samsung' WHERE Manufacturer ='Samsung Inc.';",
                "UPDATE",
            ],
            'Command DELETE' => [
                "DELETE FROM Products WHERE Manufacturer='Huawei';",
                "DELETE",
            ],
            'Command SELECT' => [
                "SELECT * FROM Products;",
                "SELECT",
            ],
        ];
    }

    /**
     * @test
     * @dataProvider providerSetQueryException
     * @depends test_setQuery_withParameters_shouldSaveTheQueryAndTheCommand
     */
    public function test_setQuery_withParameters_shouldReturnAnException(
        string $query,
        \Exception $objException
    ): void {
        $this->expectException($objException::class);
        $this->mySqlOperatorsFake->setQuery($query);
    }

    /**
     * @return array<string,mixed>
     */
    public function providerSetQueryException(): array
    {
        return [
            'Without command' => [
                "Products SET Manufacturer = 'Samsung' WHERE Manufacturer ='Samsung Inc.';",
                new QueryBuilderException(),
            ],
            'Command other than UPDATE, SELECT, DELETE' => [
                "CREATE TABLE Customers(Id INT,Age INT,FirstName VARCHAR(20),LastName VARCHAR(20));",
                new QueryBuilderException(),
            ],
        ];
    }

    /**
     * @test
     * @depends test_setQuery_withParameters_shouldSaveTheQueryAndTheCommand
     */
    public function test_in_withParameterNot_shouldChangeInternalParameterQuery(): void
    {
        $expected = "SELECT * FROM Products WHERE Manufacturer NOT IN ('Samsung','HTC','Huawei')";
        $query = 'SELECT * FROM Products WHERE Manufacturer';
        $this->mySqlOperatorsFake->setQuery($query);
        $this->mySqlOperatorsFake->in(
            [
                "'Samsung'",
                "'HTC'"
            ],
            true
        );
        $this->mySqlOperatorsFake->in("'Huawei'");
        $this->assertEquals($expected, $this->mySqlOperatorsFake->getQueryFake());
    }

    /**
     * @test
     * @depends test_setQuery_withParameters_shouldSaveTheQueryAndTheCommand
     */
    public function test_like_withParameterNot_shouldChangeInternalParameterQuery(): void
    {
        $expected = "SELECT * FROM Products WHERE ProductName NOT LIKE '_Phone%'";
        $query = 'SELECT * FROM Products WHERE ProductName';
        $this->mySqlOperatorsFake->setQuery($query);
        $this->mySqlOperatorsFake->like('_Phone%', true);
        $this->assertEquals($expected, $this->mySqlOperatorsFake->getQueryFake());
    }

    /**
     * @test
     * @depends test_setQuery_withParameters_shouldSaveTheQueryAndTheCommand
     */
    public function test_regex_withParameterNot_shouldChangeInternalParameterQuery(): void
    {
        $expected = "SELECT * FROM Products WHERE ProductName NOT REGEXP 'iPhone [78]'";
        $query = 'SELECT * FROM Products WHERE ProductName';
        $this->mySqlOperatorsFake->setQuery($query);
        $this->mySqlOperatorsFake->regex('iPhone [78]', true);
        $this->assertEquals($expected, $this->mySqlOperatorsFake->getQueryFake());
    }
}
