<?php

/**
 * ZeroPress, a WordPress clone implemented in Zerolith
 */

namespace Zeropress;

use Zeropress\Entity\User;


class Engine {
    const VERSION = "v?";
    
    private $user = null;

    private string $previousRequestId;
    private string $requestId;

    public function __construct(string $requestId, array $cookies) {
        $this->previousRequestId = $_SESSION["requestId"] ?? "";
        $this->requestId = $requestId;
        
        $_SESSION["requestId"] = $this->requestId;
        
        if (isset($cookies["zp-user"])) {
            $this->user = User::fromCookie($cookies["zp-user"]);
        }
    }

    private function registerRequest(array $purposes, string $requestId) : void {
        foreach($purposes as $purpose) {
            $this->requestIds[$purpose] = $this->requestIds[$purpose] ?? [];
            array_push($this->requestIds[$purpose], $requestId);
        }
    }

    public function authenticate($username, $password) : ?string {
        $this->user = User::fromCredentials($username, $password);
        return $this->user->cookie;
    }
    
    public function isAuthenticated() : bool {
        return $this->user != null;
    }
    
    public function isAdmin() : bool {
        return $this->user && in_array("administrator", $this->user?->roles ?? []);
    }

    public function isValidRequest(string $csrf) : bool {
        return hash_equals($csrf, $this->previousRequestId);
    }

    public function requestId() : string {
        return $this->requestId;
    }
    
    public function getVersion() : string {
        return self::VERSION;
    }
    
}
