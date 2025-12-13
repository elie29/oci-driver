<?php

declare(strict_types=1);

namespace Elie\OCI\Driver;

class Connection
{
    protected string $user;
    protected string $password;
    protected string $dbname;
    protected string $charset;

    /**
     * @param string $user The Oracle username.
     * @param string $password The password for username.
     * @param string $dbname Database connection string or name.
     * @param string $charset Database connection charset: default UTF8
     */
    public function __construct(string $user, string $password, string $dbname, string $charset = 'UTF8')
    {
        $this->user = $user;
        $this->password = $password;
        $this->dbname = $dbname;
        $this->charset = $charset;
    }

    /**
     * @return resource
     * @throws DriverException
     */
    public function connect()
    {
        $connection = @oci_pconnect($this->user, $this->password, $this->dbname, $this->charset);

        if (!$connection) {
            $message = sprintf('Connection error %s, %s', $this->user, $this->dbname);
            throw new DriverException($message);
        }

        return $connection;
    }
}
