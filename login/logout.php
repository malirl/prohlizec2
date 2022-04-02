<?php

require_once "../_includes/bootstrap.inc.php";

class Page
{
    public function __construct()
    {
        session_start();
        unset($_SESSION["user"]);
        header("Location:".  $_SERVER['DOCUMENT_ROOT']."/login/login.php");
    }

}
(new Page());
