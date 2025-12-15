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
            $error = oci_error();
            $message = sprintf(
                'Connection error for user %s to %s: %s',
                $this->user,
                $this->dbname,
                $error['message'] ?? 'Unknown error'
            );
            throw new DriverException($message);
        }

        return $connection;
    }
}
