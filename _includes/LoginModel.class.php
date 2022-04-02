<?php

class LoginModel
{

    public $login, $pswd;
    public array $validationErrors = [];


    public function __construct($login, $pswd)
    {
        $this->login = $login;
        $this->pswd = $pswd;
    }


    public function getLogin()
    {
        return filter_input(
            INPUT_POST, "login"
        ) ?: null;
    }

    public function getPswd()
    {
        return filter_input(
            INPUT_POST, "pswd"
        ) ?: null;
    }

    public function getNewPswd()
    {
        return filter_input(
            INPUT_POST, "newPswd"
        ) ?: null;
    }

    public function getFromPost()
    {
        // současně ošetři sql injection
        return new self(self::getLogin(), self::getPswd());
    }


    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }

    public function auth()
    {
        $sql = "SELECT pswd FROM employee WHERE login=:login";
        $stmt = DB::getConnection()->prepare($sql);
        $stmt->bindParam(':login', $this->login);
        $stmt->execute();
        $pswd = $stmt->fetch()->pswd;

        if (password_verify($this->pswd, $pswd)) {
            return true;
        }
        $this->validationErrors["currentPswd"] = "špatné heslo";
        return false;
    }

    public function setAccess($user)
    {
        session_start();
        $_SESSION["user"] = $user;
        $_SESSION["userLogin"] = $this->login;

    }

    public function isAdmin()
    {
        $sql = "SELECT admin FROM employee WHERE login=:login";

        $stmt = DB::getConnection()->prepare($sql);
        $stmt->bindParam(':login', $this->login);
        $stmt->execute();

        return $stmt->fetch()->admin;
    }

    public function loginExists()
    {
        $sql = "SELECT login FROM employee WHERE login=:login";
        $stmt = DB::getConnection()->prepare($sql);
        $stmt->bindParam(':login', $this->login);
        $stmt->execute();
        while ($row = $stmt->fetch()) {
            return true;
        }

        return false;
    }

    public function saveNewPswd()
    {
        $options = [
        'cost' => 10
        ];

        $pass_hash = password_hash($this->getNewPswd(), PASSWORD_BCRYPT, $options);

        $sql = "UPDATE employee SET pswd=:pswd WHERE login=:login";

        $stmt = DB::getConnection()->prepare($sql);
        $stmt->bindParam(':login', $this->login);
        $stmt->bindParam(':pswd', $pass_hash);
        $stmt->execute();

        return true;

    }


}
