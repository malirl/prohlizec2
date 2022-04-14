<?php

require_once "../_includes/bootstrap.inc.php";

final class Page extends BaseDBPage
{

    const STATE_FORM_REQUESTED = 1;
    const STATE_DATA_SENT = 2;
    const STATE_REPORT_RESULT = 3;

    const RESULT_SUCCESS = 1;
    const RESULT_FAIL = 2;
    const RESULT_EMPLOYEE_NOT_FOUND = 3;

    private EmployeeModel $employee;
    private int $state, $result;

    public function __construct()
    {
        parent::__construct(self::OPERATION_EDIT);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->getState();

        $this->employee = EmployeeModel::getFromPost();

        if ($this->state === self::STATE_REPORT_RESULT) {
            switch ($this->result) {
            case self::RESULT_SUCCESS:
                $this->title = "Zaměstnanec aktualizován";
                return;
            case self::RESULT_FAIL:
                $this->title = "Aktualizace zaměstnance selhalo";
                return;
            case self::RESULT_EMPLOYEE_NOT_FOUND:
                $this->title = "Zaměstnanec neexistuje";
                return;
            default:
                exit;
            }
        }

        if ($this->state === self::STATE_DATA_SENT) {
            if ($this->employee->validate()) {
                if ($this->employee->update($this->pswdSave)) {
                    $this->key = new KeyModel();
                    // stavajici klice odeber
                    KeyModel::deleteById($this->employee->employee_id);

                    // pridej ty, ktery mas
                    foreach ($this->employee->keys as $key => $room_id) {
                        if (!$this->key->insert($this->employee->employee_id, (int)$room_id)) {
                            $this->redirect(self::RESULT_FAIL);
                        }
                    }
                    $this->redirect(self::RESULT_SUCCESS);
                } else {
                    $this->redirect(self::RESULT_FAIL);
                }
            } else {
                $this->state = self::STATE_FORM_REQUESTED;
                $this->title = "Invalid data";
            }
        } else {
            $this->title = "Update employee";
            $this->employee = new EmployeeModel((new EmployeeModel)->getById(filter_input(INPUT_GET, "employee_id")));
        }



        $employeeKeys = KeyModel::getByEmployeeId(filter_input(INPUT_GET, "employee_id"));
        while ($row = $employeeKeys->fetch()) {
            $keysEmployee[] = $row->room;
        }

        foreach (RoomModel::getAll() as $key => $value) {
            $keys[] = [
              id => $value->room_id,
              name => $value->name,
              selected => in_array($value->room_id, $keysEmployee)
            ];
        }

        $this->employee->params[keys] = $keys;

    }

    private function getState() : void
    {
        $result = filter_input(INPUT_GET, "result", FILTER_VALIDATE_INT);

        switch ($result) {
        case self::RESULT_SUCCESS:
            $this->state = self::STATE_REPORT_RESULT;
            $this->result = self::RESULT_SUCCESS;
            return;
        case self::RESULT_FAIL:
            $this->state = self::STATE_REPORT_RESULT;
            $this->result = self::RESULT_FAIL;
            return;
        case self::RESULT_EMPLOYEE_NOT_FOUND:
            $this->state = self::STATE_REPORT_RESULT;
            $this->result = self::RESULT_EMPLOYEE_NOT_FOUND;
            return;
        default:
            break;
        }


        if ($_SERVER['REQUEST_METHOD'] === 'POST'
            && !EmployeeModel::exists($_POST["employee_id"])
            || ($_SERVER['REQUEST_METHOD'] === 'GET'
            && !EmployeeModel::exists($_GET["employee_id"]))
        ) {
            $this->redirect(self::RESULT_EMPLOYEE_NOT_FOUND);
        }


        //byl odeslán formulář
        $action = filter_input(INPUT_POST, "action");
        if ($action === "update") {
            $this->state = self::STATE_DATA_SENT;
            $this->pswdSave = filter_var($_POST["setPswd"], FILTER_VALIDATE_BOOLEAN);
            return;
        }

        $this->state = self::STATE_FORM_REQUESTED;
    }

    protected function body(): string
    {

        $getRooms = RoomModel::getAll();
        while ($row = $getRooms->fetch()) {
            $rooms[$row->room_id] = [
            room_id => $row->room_id,
            name => $row->name
            ];
        }

        unset($rooms[$this->employee->room]);

        array_unshift(
            $rooms,
            [
            room_id => $this->employee->room,
            name => RoomModel::getById($this->employee->room)[name]
            ]
        );


        if ($this->state === self::STATE_FORM_REQUESTED) {
            return $this->m->render(
                "employeeForm", [
                "employee"=>$this->employee->params,
                "rooms"=> $rooms,
                "keys"=> RoomModel::getAll(),
                "errors"=>$this->employee->getValidationErrors(),
                "update"=>true,
                "employee_id" => $_GET["employee_id"]
                ]
            );

        } elseif ($this->state === self::STATE_REPORT_RESULT) {
            switch ($this->result) {
            case self::RESULT_SUCCESS:
                return $this->m->render(
                    "reportSuccess",
                    ["data"=>"Employee updated successfully",
                    "link" => "./",
                    "name" => "employee list"
                    ]
                );
            case self::RESULT_FAIL:
                return $this->m->render(
                    "reportFail",
                    ["data"=>"Employee update failed. Please contact adiministrator or try again later.",
                    "link" => "./",
                    "name" => "employee list"
                    ]
                );
            case self::RESULT_EMPLOYEE_NOT_FOUND:
                return $this->m->render(
                    "reportFail",
                    ["data"=>"Employee not found.",
                    "link" => "./",
                    "name" => "employee list"
                    ]
                );
            default:
                exit;
            }
        }
    }
}

(new Page())->render();
