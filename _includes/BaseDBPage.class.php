<?php

abstract class BaseDBPage extends BasePage
{

    protected PDO $pdo;
    protected User $user;

    const OPERATION_VIEW = 1;
    const OPERATION_EDIT = 2;
    const OPERATION_CHANGE_PSWD = OPERATION_VIEW;
    const OPERATION_NONE = 3;

    public function __construct($operation = self::OPERATION_VIEW)
    {
        parent::__construct();
        $this->user = new User();
        if (!$this->user->hasAccess($operation)) {
            $this->redirectToLoginPage();
        }
    }

    protected function validate()
    {

    }







}
