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
        $rows = zdb::getArraySafe(
            "SELECT user_login, user_email FROM zp_users WHERE session = ?;",
            [$token]
        );
        
        if (! $rows) {
            return null;
            // throw new Exception("Failed to authenticate cookie!");
        }

        $row = end($rows);
        return new self($row["user_login"], $row["user_email"], $token);
    }

    public static function fromCredentials(string $handle, string $secret) : ?User {
        $rows = zdb::getArraySafe(
            "SELECT user_login, user_email FROM zp_users WHERE user_login = ? AND user_pass = ?;",
            [$handle, password_hash($secret, PASSWORD_BCRYPT)]
        );
        
        if (1 > count($rows)) {
            // throw new Exception("Failed to authenticate credentials!");
            return null;
        }

        $row = end($rows);
        return new self($row["user_login"], $row["user_email"], bin2hex(random_bytes(32)));
    }
}
