<?php

declare(strict_types=1);

namespace vadimcontenthunter\MyDB\MySQL\MySQLQueryBuilder\TableMySQLQueryBuilder\Operators;

use vadimcontenthunter\MyDB\Exceptions\QueryBuilderException;
use vadimcontenthunter\MyDB\Interfaces\SQLQueryBuilder\SQLQueryBuilder;
use vadimcontenthunter\MyDB\Interfaces\SQLQueryBuilder\TableSQLQueryBuilder\Operators\Operators;
use vadimcontenthunter\MyDB\Interfaces\SQLQueryBuilder\TableSQLQueryBuilder\Operators\OperatorOptionsCreate;

/**
 * @author    Vadim Volkovskyi <project.k.vadim@gmail.com>
 * @copyright (c) Vadim Volkovskyi 2022
 */
class MySqlOperatorOptionsCreate implements OperatorOptionsCreate
{
    protected string $query = '';

    /**
     * @param  array<string> $field_attribute
     * @throws QueryBuilderException
     */
    public function addField(string $field_name, string $data_type, array $field_attribute = []): OperatorOptionsCreate
    {
        $field_attribute_str = count($field_attribute) > 0 ? ' ' . implode(" ", $field_attribute) : '';

        if (preg_match("~^CREATE TABLE \w+\s?\((?<values>.+)\).*~iu", $this->query, $matches)) {
            $values = $matches['values'] . ',' . $field_name . ' ' . $data_type . $field_attribute_str;
            $this->query = preg_replace('~' . preg_quote($matches['values'], '/') . '~u', $values, $this->query)
                            ?? throw new QueryBuilderException('Error, incorrect value.');
        } else {
            $this->query .= '(' . $field_name . ' ' . $data_type . $field_attribute_str . ')';
        }
        return $this;
    }

    /**
     * @throws QueryBuilderException
     */
    public function consrtaintCheck(string $consrtaint_name, string $value_a, string $operator, string $value_b): OperatorOptionsCreate
    {
        if (
            preg_match(
                "~^CREATE\sTABLE\s\w+\s?\(.+CONSTRAINT\scustomer_age_chk\s(?<values>CHECK\s?\([\w\s<>()=]+)\).*\);?$~iu",
                $this->query,
                $matches
            )
        ) {
            $values = $matches['values'] . ' AND (' . $value_a . ' ' . $operator . ' ' . $value_b . ')';
            $this->query = preg_replace('~' . preg_quote($matches['values'], '/') . '~u', $values, $this->query)
                            ?? throw new QueryBuilderException('Error, incorrect value.');
        } elseif (preg_match("~CONSTRAINT\s" . $consrtaint_name . "\s~iu", $this->query)) {
            throw new QueryBuilderException('Error, name for CONSTRAINT already taken.');
        } elseif (preg_match("~^CREATE TABLE \w+\s?\((?<values>.+)\).*~iu", $this->query, $matches)) {
            $check = 'CONSTRAINT ' . $consrtaint_name . ' CHECK((' . $value_a . ' ' . $operator . ' ' . $value_b . '))';
            $values = $matches['values'] . ',' . $check;
            $this->query = preg_replace('~' . preg_quote($matches['values'], '/') . '~u', $values, $this->query)
                            ?? throw new QueryBuilderException('Error, incorrect value.');
        } else {
            throw new QueryBuilderException('Error, incorrect value.');
        }

        return $this;
    }

    /**
     * @param  string[] $field_names
     * @throws QueryBuilderException
     */
    public function consrtaintUnique(string $consrtaint_name, array $field_names): OperatorOptionsCreate
    {
        if (
            preg_match(
                "~^CREATE\sTABLE\s\w+\s?\(.+CONSTRAINT\scustomer_phone_uq\s(?<values>UNIQUE\s?\([\w,\s]+)\).*\);?$~iu",
                $this->query,
                $matches
            )
        ) {
            $values = $matches['values'] . ',' . implode(",", $field_names);
            $this->query = preg_replace('~' . preg_quote($matches['values'], '/') . '~u', $values, $this->query)
                            ?? throw new QueryBuilderException('Error, incorrect value.');
        } elseif (preg_match("~CONSTRAINT\s" . $consrtaint_name . "\s~iu", $this->query)) {
            throw new QueryBuilderException('Error, name for CONSTRAINT already taken.');
        } elseif (preg_match("~^CREATE TABLE \w+\s?\((?<values>.+)\).*~iu", $this->query, $matches)) {
            $check = 'CONSTRAINT ' . $consrtaint_name . ' UNIQUE(' . implode(",", $field_names) . ')';
            $values = $matches['values'] . ',' . $check;
            $this->query = preg_replace('~' . preg_quote($matches['values'], '/') . '~u', $values, $this->query)
                           ?? throw new QueryBuilderException('Error, incorrect value.');
        } else {
            throw new QueryBuilderException('Error, incorrect value.');
        }

        return $this;
    }

    /**
     * @param string[] $fields
     * @param string[] $referencesFields
     * @param string[] $attributes
     */
    public function consrtaintForeignKey(
        string $consrtaint_name,
        array $fields,
        string $referencesTableName,
        array $referencesFields,
        array $attributes
    ): OperatorOptionsCreate {

        return $this;
    }

    public function setQuery(string $query): SQLQueryBuilder
    {
        $this->query = $query;
        return $this;
    }

    public function getQuery(): string
    {
        return $this->query;
    }
}
