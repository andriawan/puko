<?php

namespace Puko\Core\Router;

class RouteParser
{

    /**
     * @var string
     */
    public $FunctionNames;

    /**
     * @var string
     */
    public $ClassName;

    /**
     * @var string
     */
    private $RawRouter;

    /**
     * @var int
     */
    private $ConstructVars;

    /**
     * @var array
     */
    private $FunctionVars = array();

    public function __construct($router)
    {
        $this->RawRouter = $router;
        $variables = explode('/', $this->RawRouter);

        $this->FunctionNames = 'main';

        foreach ($variables as $key => $val) {
            if($val == '')
                break;

            switch ($key) {
                case 0:
                    $this->ClassName = $val;
                    break;
                case 1:
                    if (intval($variables[1]))
                        $this->ConstructVars = $val;
                    else
                        $this->FunctionNames = $val;
                    break;
                case 2:
                    if (isset($this->ConstructVars) || is_int($this->ConstructVars))
                        $this->FunctionNames = $val;
                    else
                        array_push($this->FunctionVars, $val);
                    break;
                default:
                    array_push($this->FunctionVars, $val);
                    break;
            }
        }
    }

    private function ClassLoader($ClassName)
    {
        $import = FILE . CONTROLLERS . $ClassName . '.php';
        if (!file_exists($import))
            die('Controller file ' . $this->ClassName . ' is not found.');
        require_once($import);
    }

    public function InitializeClass($authCode)
    {
        $this->ClassLoader($this->ClassName);
        return new $this->ClassName($this->ConstructVars, $authCode);
    }

    public function InitializeFunction($object)
    {
        if (method_exists($object, $this->FunctionNames)&& is_callable(array($object, $this->FunctionNames))) {
            if (empty($this->FunctionVars))
                return call_user_func(array($object, $this->FunctionNames));
            else
                return call_user_func_array(array($object, $this->FunctionNames), $this->FunctionVars);
        } else
            die('Method ' . $this->FunctionNames . ' not found');
    }
}