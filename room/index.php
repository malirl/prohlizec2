<?php

require_once "../_includes/bootstrap.inc.php";

final class Page extends BaseDBPage
{
    public function __construct()
    {
        parent::__construct();
        $this->title = "Room listing";
    }


    protected function body(): string
    {
        return $this->m->render(
            "roomList",
            ["rooms" => RoomModel::getAll(),
            "roomDetailName" => "roomDetail.php",
            "allowEdit" => $this->user->hasAccess(self::OPERATION_EDIT)
            ]
        );
    }
}

(new Page())->render();
