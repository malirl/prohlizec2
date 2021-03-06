<?php

require_once "../_includes/bootstrap.inc.php";

final class Page extends BaseDBPage
{

    const STATE_REPORT_RESULT = 3;
    const STATE_DELETE_REQUESTED = 4;

    const RESULT_SUCCESS = 1;
    const RESULT_FAIL = 2;

    private int $state, $result;

    public function __construct()
    {
        parent::__construct(self::OPERATION_EDIT);
        $this->title = "Room delete";
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->getState();

        if ($this->state === self::STATE_REPORT_RESULT) {
            if ($this->result === self::RESULT_SUCCESS) {
                $this->title = "Room deleted";
            } else {
                $this->title = "Room deletion failed";
            }
            return;
        }

        if ($this->state === self::STATE_DELETE_REQUESTED) {
            $employeeId = filter_input(INPUT_POST, "employee_id", FILTER_VALIDATE_INT);
            if ($employeeId) {
                if (EmployeeModel::deleteById($employeeId)) {
                    $this->redirect(self::RESULT_SUCCESS);
                } else {
                    $this->redirect(self::RESULT_FAIL);
                }
            } else {
                throw new RequestException(400);
            }

        }

    }


    protected function body(): string
    {
        if ($this->state === self::STATE_REPORT_RESULT) {
            if ($this->result === self::RESULT_SUCCESS) {
                return $this->m->render(
                    "reportSuccess",
                    ["data"=>"Employee deleted successfully",
                    "link" => "./",
                    "name" => "employee list"
                    ]
                );
            } else {
                return $this->m->render(
                    "reportFail",
                    ["data"=>"Employee deletion failed. Please contact adiministrator or try again later.",
                    "link" => "./",
                    "name" => "employee list"
                    ]
                );
            }
        }
        return "";
    }

    private function getState() : void
    {
        //je u?? hotovo?
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

        // if ($_SERVER['REQUEST_METHOD'] === 'GET'
        //     && !EmployeeModel::exists($_GET["employee_id"])
        // ) {
        //   exit;
        // }


        $this->state = self::STATE_DELETE_REQUESTED;
    }

}

(new Page())->render();
