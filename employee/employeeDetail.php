<?php

require_once "../_includes/bootstrap.inc.php";

final class Page extends BaseDBPage
{

    public function __construct()
    {
        parent::__construct();
        $this->title = "Employee detail";

    }

    protected function body(): string
    {
        return $this->m->render(
            "employeeDetail",
            ["employee" => $this->employee->getById()->params]
        );
    }

    protected function setUp() : void
    {
        parent::setUp();
        $this->employee = EmployeeModelDetail::getFromGet();

        if ($this->employee->validate()) {
            // (new ErrorPage($this->employee->getValidationErrors()))->render();
        }
    }
}


(new Page())->render();
