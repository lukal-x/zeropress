<?php

require_once "../main.php";

use Zeropress\Engine;

class Screen {

    protected $zp;

    public function __construct(Engine $zp) {
        $this->zp = $zp;
    }
    
    public function login() : array {
        if ($this->zp->isAuthenticated() && $this->zp->isAdmin()) {
            return ["/zp-admin", null];
        }
        
        if ($this->zp->isAuthenticated() && !$this->zp->isAdmin()) {
            return ["/", null];
        }

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $login = zfilter::array("user|pass|token", "stringSafe");
            list("user" => $handle, "pass" => $secret, "token" => $csrf) = $login;
            
            return $this->authenticate($handle, $secret, $csrf);
        }

        return [null, null];
    }

    private function authenticate($handle, $secret, $csrf) : array {
        if (! $this->zp->isValidRequest($csrf)) {
            return [null, "Invalid request!"];   
        }

        if (! (4 < strlen($handle) && strlen($handle) < 51)) {
            return [null, "Invalid username!"];
        }

        if (! (4 < strlen($secret) && strlen($secret) < 51)) {
            return [null, "Invalid credentials!"];
        }

        if ($handle && $secret) {
            $cookie = $this->zp->authenticate($handle, $secret, $csrf);
            if (! $cookie) {
                return [null, "Failed to authenticate!"];
            }

            setcookie("zp-user", $cookie, [
                "httponly" => true,
                // "secure" => true,
                "samesite" => "Strict",
            ]);
            return ["", null];
        }
    }
}

$screen = new Screen($zp);

[$redirect, $error] = $screen->login($zp);

if ($redirect) {
    zpage::redirect($redirect);
}

?>

    <form method="POST" action="" >
    <input type="text" name="user" placeholder="Username" />
      <input type="hidden" name="token" value="<?php print $zp->requestId(); ?>" />
      <input type="password" name="pass" placeholder="Password" />
      <input type="submit" />
    </form>
    <b><?php print $error ?></b>
