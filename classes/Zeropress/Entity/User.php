<?php

namespace Zeropress\Entity;

use Exception;
use zdb;

class User {

    protected ?string $name = null;
    protected ?string $email = null;
    protected ?string $cookie = null;
    
    public function __construct(string $name, string $email, string $cookie) {
        $this->name = $name;
        $this->email = $email;
        $this->cookie = $cookie;
    }

    public static function fromCookie(string $token) : ?User {
        $row = zdb::getArray(
            "SELECT name, email FROM users WHERE session = :token;",
            ["token" => $token]
        );
        
        if (! $row) {
            throw new Exception("Failed to authenticate cookie!");
        }

        return new self($row["name"], $row["email"], $token);
    }

    public static function fromCredentials(string $handle, string $secret) : ?User {
        $row = zdb::getArray(
            "SELECT name, email FROM users WHERE handle = :handle AND secret = :secret;",
            ["handle" => $handle, "secret" => password_hash($secret)]
        );
        
        if (! $row) {
            throw new Exception("Failed to authenticate credentials!");
        }

        return new self($row["name"], $row["email"], bin2hex(random_bytes(32)));
    }
}
