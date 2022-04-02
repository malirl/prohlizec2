<?php

abstract class BasePage
{
    protected MustacheRunner $m;
    public string $title;

    public function __construct()
    {
        $this->m = new MustacheRunner();
    }

    public function render() : void
    {
        try {
            $this->setUp();

            $html = $this->header();
            $html .= $this->body();
            $html .= $this->footer();
            echo $html;

            $this->wrapUp();
            exit;
        } catch (RequestException $e) {
            $ePage = new ErrorPage($e->getCode());
            $ePage->render();
        }
        catch (Exception $e) {
            if (LocalConfig::DEBUG) {
                dump($e);
            } else {
                $ePage = new ErrorPage();
                $ePage->render();
            }
        }
    }

    protected function setUp() : void
    {
    }

    protected function header() : string
    {
        return $this->m->render("head", ["title" => $this->title]);
    }

    abstract protected function body() : string;

    protected function footer() : string
    {
        return $this->m->render(
            "foot",
            ["logout" => $_SERVER['DOCUMENT_ROOT']."/login/logout.php"]
        );

    }

    public function redirectToLoginPage()
    {
        header("Location:". $_SERVER['DOCUMENT_ROOT']."/login/login.php");
    }

    protected function redirect(int $result) : void
    {
        //odkaz sám na sebe, bez query string atd.
        $location = strtok($_SERVER['REQUEST_URI'], '?');

        header("Location: {$location}?result={$result}");
        exit;
    }


    protected function wrapUp() : void
    {
    }
}
