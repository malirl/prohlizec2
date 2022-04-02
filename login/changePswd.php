<?php
require_once "../_includes/bootstrap.inc.php";


final class Page extends BaseDBPage
{
    const STATE_FORM_REQUESTED = 1;
    const STATE_FORM_SENT = 2;
    const STATE_REPORT_RESULT = 3;

    const RESULT_SUCCESS = 1;

    public array $validationErrors = [];
    private int $state, $result = self::RESULT_SUCCESS;


    public function __construct()
    {
        parent::__construct(self::OPERATION_CHANGE_PSWD);
        $this->title = "Change password";
    }

    protected function setUp() : void
    {
        parent::setUp();
        $this->getState();

        if ($this->state === self::STATE_FORM_SENT) {
            if ($this->login->auth()) {
                $this->login->saveNewPswd();
                $this->result = self::RESULT_SUCCESS;
                $this->redirect($this->result);
            }
        }


    }



    private function getState() : void
    {
        $this->login = null;

        if (empty($_POST)) {
            $this->state = self::STATE_FORM_REQUESTED;
        }
        else {
            $this->login = new LoginModel($_SESSION["userLogin"], LoginModel::getPswd());
            $this->state = self::STATE_FORM_SENT;
        }
        if (isset($_GET["result"])) {
            $this->state = self::STATE_REPORT_RESULT;
        }
    }


    protected function body(): string
    {

        if ($this->state === self::STATE_REPORT_RESULT
            && $this->result === self::RESULT_SUCCESS
        ) {
            return $this->m->render(
                "reportSuccess",
                ["data" => "heslo úspěšně změněno"]
            );
        }

        return $this->m->render(
            "formChangePswd",
            ["errors" => ($this->state === self::STATE_FORM_REQUESTED) ? [] : $this->login->getValidationErrors()]
            // $this->login->getValidationErrors()
            // $this->login->validationErrors
            // $this->login->getValidationErrors()
        );

    }
}
(new Page())->render();
