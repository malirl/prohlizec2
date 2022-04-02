<?php

require_once "../_includes/bootstrap.inc.php";

class Create extends BaseDBPage
{
    const STATE_FORM_REQUESTED = 1;
    const STATE_DATA_SENT = 2;
    const STATE_REPORT_RESULT = 3;

    const RESULT_SUCCESS = 1;
    const RESULT_FAIL = 2;

    private RoomModel $room;
    private int $state, $result;

    public function __construct()
    {
        parent::__construct(self::OPERATION_EDIT);
        $this->title = "Create room";
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->getState();

        if ($this->state === self::STATE_REPORT_RESULT) {
            if ($this->result === self::RESULT_SUCCESS) {
                $this->title = "Room created";
            } else {
                $this->title = "Room creation failed";
            }
            return;
        }

        if ($this->state === self::STATE_DATA_SENT) {
            $this->room = RoomModel::getFromPost();
            if ($this->room->validate()) {
                if ($this->room->insert()) {
                    $this->redirect(self::RESULT_SUCCESS);
                } else {
                    $this->redirect(self::RESULT_FAIL);
                }
            } else {
                $this->state = self::STATE_FORM_REQUESTED;
                $this->title = "Invalid data";
            }
        } else {
            $this->title = "Create new room";
            $this->room = new RoomModel();
        }

    }


    protected function body(): string
    {
        if ($this->state === self::STATE_FORM_REQUESTED) {
            return $this->m->render(
                "roomForm", [
                "room"=>$this->room,
                "errors"=>$this->room->getValidationErrors(),
                "create"=>true
                ]
            );
        } elseif ($this->state === self::STATE_REPORT_RESULT) {
            if ($this->result === self::RESULT_SUCCESS) {
                return $this->m->render(
                    "reportSuccess",
                    ["data"=>"Room created successfully",
                    "link" => "./",
                    "name" => "room list"
                    ]
                );
            } else {
                return $this->m->render(
                    "reportFail",
                    ["data"=>"Room creation failed. Please contact adiministrator or try again later.",
                    "link" => "./",
                    "name" => "room list"
                    ]
                );
            }

        }
    }

    private function getState() : void
    {
        //je už hotovo?
        $result = filter_input(INPUT_GET, "result", FILTER_VALIDATE_INT);
        if ($result === self::RESULT_SUCCESS) {
            $this->state = self::STATE_REPORT_RESULT;
            $this->result = self::RESULT_SUCCESS;
            return;
        } elseif ($result === self::RESULT_FAIL) {
            $this->state = self::STATE_REPORT_RESULT;
            $this->result = self::RESULT_FAIL;
            return;
        }

        //byl odeslán formulář
        $action = filter_input(INPUT_POST, "action");
        if ($action === "create") {
            $this->state = self::STATE_DATA_SENT;
            return;
        }

        $this->state = self::STATE_FORM_REQUESTED;
    }
}

(new Create())->render();
