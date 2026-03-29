<?php
include("inc/classes/DB.php");
include_once("inc/classes/session.php");

class User {
    private $db;

    public function __construct() {
        $this->db = new DB();
    }

    /**
     * Handles user registration
     */
    public function userRegistration($data) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register-submit'])) {
            $username = trim($data['username']);
            $email = trim($data['email']);
            $password = $data['password'];
            $confirm_password = $data['confirm-password'];

            // Basic validation
            if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
                return '<div class="alert alert-danger"><b>Error!</b> All fields are required.</div>';
            }

            if (strlen($username) < 3) {
                return '<div class="alert alert-danger"><b>Error!</b> Username must be at least 3 characters long.</div>';
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return '<div class="alert alert-danger"><b>Error!</b> Invalid email format.</div>';
            }

            if ($password !== $confirm_password) {
                return '<div class="alert alert-danger"><b>Error!</b> Passwords do not match.</div>';
            }

            // Check for existing email
            $checkSql = "SELECT email FROM user WHERE email = ?";
            $checkArr = [$email];
            $check = $this->db->simplequery($checkSql, $checkArr);
            if ($check && $check->rowCount() > 0) {
                return '<div class="alert alert-danger"><b>Error!</b> Email already exists.</div>';
            }

            // Store hashed password (note: consider using password_hash for stronger security)
            $hashedPassword = md5($password); // Replace with password_hash() for production

            $insertSql = "INSERT INTO user (user_name, email, password, role) VALUES (?, ?, ?, 'student')";
            $insertArr = [$username, $email, $hashedPassword];
            $insert = $this->db->simplequery($insertSql, $insertArr);

            if ($insert) {
                return '<div class="alert alert-success"><b>Success!</b> Registration completed.</div>';
            } else {
                return '<div class="alert alert-danger"><b>Error!</b> Something went wrong. Please try again.</div>';
            }
        }
    }

    /**
     * Handles user login
     */
    public function userLogin($data) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login-submit'])) {
            $email = trim($data['email']);
            $password = $data['password'];

            // Basic validation
            if (empty($email) || empty($password)) {
                return '<div class="alert alert-danger"><b>Error!</b> Email and password are required.</div>';
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return '<div class="alert alert-danger"><b>Error!</b> Invalid email format.</div>';
            }

            $hashedPassword = md5($password); // Replace with password_hash & password_verify for better security

            $sql = "SELECT * FROM ims_user WHERE email = ? AND password = ?";
            $arr = [$email, $hashedPassword];
            $query = $this->db->simplequery($sql, $arr);
            $user = $query->fetch(PDO::FETCH_OBJ);

            if ($user) {
                $session = new Session();
                $session->setSession('login', true);
                $session->setSession('user_id', $user->user_id);
                $session->setSession('user_name', $user->user_name);
                $session->setSession('email', $user->email);
                $session->setSession('role', $user->role);
                $session->setSession('loginmsg', '<div class="alert alert-success"><b>Login Successful!</b></div>');

                // Redirect by role
                switch ($user->role) {
                    case 'admin':
                        header("Location: landing.php");
                        break;
                    case 'interviewer':
                        header("Location: interviewer_home.php");
                        break;
                    case 'student':
                        header("Location: student_home.php");
                        break;
                    default:
                        header("Location: login.php");
                }
                exit();
            } else {
                return '<div class="alert alert-danger"><b>Error!</b> Invalid credentials.</div>';
            }
        }
    }
}
?>
