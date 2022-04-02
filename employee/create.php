<?php

require_once "../_includes/bootstrap.inc.php";

final class Page extends BaseDBPage
{

    const STATE_FORM_REQUESTED = 1;
    const STATE_DATA_SENT = 2;
    const STATE_REPORT_RESULT = 3;

    const RESULT_SUCCESS = 1;
    const RESULT_FAIL = 2;

    private EmployeeModel $employee;
    private int $state, $result;

    public function __construct()
    {
        parent::__construct(self::OPERATION_EDIT);
        $this->title = "Vytvoř zaměstnance";
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->getState();



        $this->employee = EmployeeModel::getFromPost();

        if ($this->state === self::STATE_REPORT_RESULT) {
            if ($this->result === self::RESULT_SUCCESS) {
                $this->title = "Zaměstnanec vytvořen";
            } else {
                $this->title = "Vytvoření zaměstnance selhalo";
            }
            return;
        }

        if ($this->state === self::STATE_DATA_SENT) {

            $this->employee = EmployeeModel::getFromPost();
            // echo var_dump($this->employee->params);
            // exit;
            if ($this->employee->validate()) {
                if ($this->employee->insert()) {
                    $this->key = new KeyModel();
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
            $this->title = "Create new employee";
            $this->employee = new EmployeeModel();
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

    protected function body(): string
    {
        if ($this->state === self::STATE_FORM_REQUESTED) {
            return $this->m->render(
                "employeeForm", [
                "employee"=>$this->employee->params,
                "rooms"=> RoomModel::getAll(),
                "keys"=> RoomModel::getAll(),
                "errors"=>$this->employee->getValidationErrors(),
                "create"=>true
                ]
            );
        } elseif ($this->state === self::STATE_REPORT_RESULT) {
            if ($this->result === self::RESULT_SUCCESS) {
                return $this->m->render("reportSuccess", ["data"=>"Room created successfully"]);
            } else {
                return $this->m->render("reportFail", ["data"=>"Room creation failed. Please contact adiministrator or try again later."]);
            }

        }
    }

}

(new Page())->render();
