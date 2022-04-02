<?php

require_once "_includes/bootstrap.inc.php";

final class Page extends BaseDBPage
{
    public function __construct()
    {
        parent::__construct();
        $this->title = "Homepage";
    }

    protected function setUp(): void
    {
        parent::setUp();

        if (!$this->user->hasAccess(self::OPERATION_VIEW)) {
            $this->redirectToLoginPage();
        }
    }

    protected function body(): string
    {

        return $this->m->render(
            "index",
            ["roomList" => $_SERVER['DOCUMENT_ROOT']."/room",
            "employeeList" => $_SERVER['DOCUMENT_ROOT']."/employee",
            "changePswd" => $_SERVER['DOCUMENT_ROOT']."/login/changePswd.php"]
        );
    }
}

(new Page())->render();
