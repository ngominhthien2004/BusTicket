<?php

namespace Ct27501Project\Controllers;

use League\Plates\Engine;

class Controller
{
    protected $view;

    public function __construct()
    {
        require_once __DIR__ . '/../functions.php';
        $this->view = new Engine(__DIR__ . '/../views');
    }

    public function sendPage($page, array $data = [])
    {
        exit($this->view->render($page, $data));
    }

    public function sendNotFound()
    {
        http_response_code(404);
    }

    // Lưu các giá trị của form được cho trong $data vào $_SESSION 
    protected function saveFormValues(array $data, array $except = [])
    {
        $form = [];
        foreach ($data as $key => $value) {
            if (!in_array($key, $except, true)) {
                $form[$key] = $value;
            }
        }
        $_SESSION['form'] = $form;
    }

    protected function getSavedFormValues()
    {
        return session_get_once('form', []);
    }
}
