<?php

class User
{
    const NONE = 1;
    const USER = 2;
    const ADMIN = 3;

    const RIGHTS_NONE = 1;
    const RIGHTS_VIEW = 2;
    const RIGHTS_CHANGE_PSWD = RIGHTS_VIEW;
    const RIGHTS_EDIT = 3;

    private $user, $rights;

    public function __construct()
    {
        $this->setRights($this->getUser());
    }

    public function getUser()
    {
        session_start();
        return $_SESSION["user"] ?: self::NONE;
    }

    public function hasAccess($operation)
    {
        switch ($operation) {
        case BaseDBPage::OPERATION_VIEW:
        case BaseDBPage::OPERATION_CHANGE_PSWD:
            switch ($this->rights) {
            case self::RIGHTS_NONE:
                $access = false;
                break;
            default:
                $access = true;
                break;
            }
            break;
        case BaseDBPage::OPERATION_EDIT;
            switch ($this->rights) {
            case self::RIGHTS_EDIT:
                $access = true;
                break;
            default:
                $access = false;
                break;
            }
            break;
        case BaseDBPage::OPERATION_NONE;
            $access = true;
            break;
        }
        return $access;
    }


    public function setRights($user)
    {
        switch ($user) {
        case self::USER:
            $rights = self::RIGHTS_VIEW;
            break;
        case self::ADMIN:
            $rights = self::RIGHTS_EDIT;
            break;
        default:
            $rights = self::RIGHTS_NONE;
            break;
        }

        $this->rights = $rights;
    }



}
