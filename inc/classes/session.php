<?php
class Session {

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function setSession($key, $value) {
        $_SESSION[$key] = $value;
    }

    public function getSession($key) {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : false;
    }

    public function unsetSession($key) {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    public function destroy() {
        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        session_destroy();
        header('Location: login.php');
        exit();
    }
}

?>
