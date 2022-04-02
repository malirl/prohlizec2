<?php
require_once "../_includes/bootstrap.inc.php";


final class Page extends BaseDBPage
{
    const STATE_ACCESS_GIVEN = 1;

    public array $validationErrors = [];


    public function __construct()
    {
        parent::__construct(self::OPERATION_NONE);
        $this->title = "Login page";
    }

    protected function setUp() : void
    {
        parent::setUp();
        $this->getState();
    }

    private function getState() : void
    {

        if (!empty($_POST)) {
            $this->login = LoginModel::getFromPost();

            // autentizace (existuje login? je heslo správně?)
            // pokud jsou chyby, ulož pokud možno údaje pro předvyplněný formulář s hláškou
            // špatný heslo/login neexistuje

            if (!$this->login->loginExists()) {
                $this->validationErrors["login"] = "invalid";
            }

            if ($this->login->auth()) {
                if ($this->login->isAdmin()) {
                    $this->login->setAccess(User::ADMIN);
                } else {
                    $this->login->setAccess(User::USER);
                }
                header("Location: ../index.php");
            } else {
                $this->validationErrors["pswd"] = "wrong";
            }
        }
    }



    protected function body(): string
    {
        return $this->m->render(
            "formLogin",
            ["errors" => $this->validationErrors, "login" => $this->login->login]
        );
    }
}


(new Page())->render();
