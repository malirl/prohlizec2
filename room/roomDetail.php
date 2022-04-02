<?php

require_once "../_includes/bootstrap.inc.php";

final class Page extends BaseDBPage
{

    public function __construct()
    {
        parent::__construct();
        $this->title = "Room detail";
    }

    protected function body(): string
    {
        return $this->m->render(
            "roomDetail",
            ["room" => RoomModelDetail::getById($this->room_id)->params]
        );
    }

    protected function setUp() : void
    {
        parent::setUp();
        $room = RoomModelDetail::getFromGet();
        $this->room_id = $room->room_id;

        if (!$room->validate()) {
            (new ErrorPage($room->validationErrors))->render();
        }
    }
}


(new Page())->render();
