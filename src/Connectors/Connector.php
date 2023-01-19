<?php

declare(strict_types=1);

namespace vadimcontenthunter\MyDB\Connectors;

use PDO;
use PDOException;
use vadimcontenthunter\MyDB\Exceptions\ConnectException;
use vadimcontenthunter\MyDB\Interfaces\ConnectorInterface;

/**
 * @author    Vadim Volkovskyi <project.k.vadim@gmail.com>
 * @copyright (c) Vadim Volkovskyi 2022
 */
class Connector implements ConnectorInterface
{
    public int $numConnected = 0;

    protected ?PDO $dataBaseHost = null;

    /**
     * @param null|array<string,mixed> $options
     */
    public function __construct(
        public ?string $dsn = null,
        public ?string $typeDb = null,
        public ?string $user = null,
        public ?string $password = null,
        public ?string $dbName = null,
        public ?string $host = null,
        public ?string $port = null,
        protected ?array $options = null
    ) {
    }

    /**
     * @return array<array<string,mixed>>
     */
    public function getOptions(): array
    {
        return $this->options ?? [];
    }

    /**
     * @param array<string,mixed> $option
     */
    public function addOption(array $option): Connector
    {
        $this->options += $option;
        return $this;
    }

    public function getNumConnected(): int
    {
        return  $this->numConnected;
    }

    /**
     * @throws PDOException
     * @throws ConnectException
     */
    public function connect(): PDO
    {
        if ($this->dataBaseHost !== null) {
            return $this->dataBaseHost;
        }

        $defaultDsn = null;
        if (
            $this->typeDb !== null
            && $this->host !== null
            && $this->dbName !== null
        ) {
            $port = $this->port !== null ? $this->port . ';' : '';
            $defaultDsn .= $this->typeDb . ':host=' . $this->host . ';' . $port . 'dbname=' . $this->dbName;
        }
        $this->dsn = $this->dsn ?? ($defaultDsn ?? throw new ConnectException("Error, not enough data to connect."));

        $this->dataBaseHost = new PDO($this->dsn, $this->user, $this->password, $this->getOptions());

        $this->numConnected++;

        return $this->dataBaseHost;
    }
}