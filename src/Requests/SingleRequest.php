<?php

declare(strict_types=1);

namespace vadimcontenthunter\MyDB\Requests;

use PDO;
use vadimcontenthunter\MyDB\Interfaces\Request;
use vadimcontenthunter\MyDB\Exceptions\MyDbException;
use vadimcontenthunter\MyDB\Interfaces\ConnectorInterface;
use vadimcontenthunter\MyDB\Interfaces\SQLQueryBuilder\SQLQueryBuilder;

/**
 * @author    Vadim Volkovskyi <project.k.vadim@gmail.com>
 * @copyright (c) Vadim Volkovskyi 2022
 */
class SingleRequest implements Request
{
    protected string $query;

    /**
     * @var array<string,mixed>
     */
    protected array $parameters = [];

    protected ?PDO $databaseHost = null;

    protected ?string $className = null;

    public function __construct(
        public ConnectorInterface $connector,
    ) {
        $this->databaseHost = $connector->connect();
    }

    public function singleQuery(SQLQueryBuilder $query_builder): SingleRequest
    {
        $this->query = $query_builder->getQuery();
        return $this;
    }

    public function addParameter(string $parameter_name, string|int $parameter_value): SingleRequest
    {
        $this->parameters += [$parameter_name => $parameter_value];
        return $this;
    }

    /**
     * @param array<string, string|int> $parameters
     */
    public function setParameters(array $parameters): SingleRequest
    {
        $this->parameters = $parameters;
        return $this;
    }

    /**
     * MyDbException
     */
    public function setClassName(string $class_name): SingleRequest
    {
        if (!class_exists($class_name)) {
            throw new MyDbException("Error, class does not exist");
        }
        $this->className = $class_name;
        return $this;
    }

    /**
     * @return array<mixed>
     */
    public function send(): array
    {
        if ($this->databaseHost === null) {
            throw new MyDbException("Error, you need to connect to the database");
        }

        $sth = $this->databaseHost->prepare($this->query);
        $sth->execute($this->parameters);
        $this->parameters = [];

        if ($this->className === null) {
            return $sth->fetchAll();
        }

        return $sth->fetchAll(PDO::FETCH_CLASS, $this->className);
    }
}
