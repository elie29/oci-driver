<?php

declare(strict_types = 1);

namespace OCI\Driver;

class Connection
{

    protected $user;
    protected $password;
    protected $dbname;
    protected $charset;

    /**
     * @param string $user The Oracle user name.
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
     *
     * @throws DriverException
     */
    public function connect()
    {
        $connection = @oci_pconnect($this->user, $this->password, $this->dbname, $this->charset);

        if (! $connection) {
            trigger_error(sprintf('Connection error %s, %s', $this->user, $this->dbname), E_USER_WARNING);
            throw new DriverException('OCI Connection error');
        }

        return $connection;
    }
}
