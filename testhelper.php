<?php

namespace TestHelper;

/**
 * @author Can Gelis <geliscan@gmail.com>
 * @copyright (c) 2013, Can Geliş
 * @license https://github.com/cangelis/laravel3-test-helper/blob/master/licence.txt MIT Licence
 * @link https://github.com/cangelis/laravel3-test-helper
 */
class TestHelper {

    /**
     * HTTP Status response code (200, 302, 404 etc)
     * @var integer 
     */
    public $statusCode;

    /**
     * View name
     * @var string
     */
    public $view;

    /**
     * Error messages from Validator class of Laravel
     * @var \Laravel\Messages
     */
    public $errors;

    /**
     * Data that are sent to the view
     * @var Array
     */
    public $data;

    /**
     * Redirect Location as string
     * @var string 
     */
    public $redirect_location;

    /**
     * Redirect params as array
     * example:
     * if redirect location = auth/login/1 then;
     * $redirect_params = array('auth','login','1')
     * @var array
     */
    public $redirect_params;

    private function __construct() {
        
    }

    /**
     * 
     * @param string $controller Controller Name
     * @param string $method Controller method name
     * @param string $request_method POST OR GET
     * @param array $post_params POST Parameters
     * @param array $get_params Parameters for Controller method
     * @return TestHelper\TestHelper Test Result
     */
    public static function runControllerTest($controller, $method, $request_method, $post_params = null, $get_params = array()) {
        $helper = new TestHelper();
        \Laravel\Request::setMethod($request_method);
        $query = \Laravel\Request::foundation()->query;
        foreach ($query->keys() as $key) {
            $query->remove($key);
        }
        if ($post_params != null)
            $query->add($post_params);
        $response = \Laravel\Routing\Controller::call($controller . "@" . $method, $get_params);
        $helper->statusCode = $response->status();
        $helper->view = $response->content->view;
        $helper->data = $response->content->data;
        $helper->errors = $response->content->errors;
        $helper->redirect_location = $response->foundation->headers->get('location');
        $remove = array(\Laravel\URL::base() . "/", \Laravel\URL::base());
        $replace = array("", "");
        $helper->redirect_params = explode("/", str_replace($remove, $replace, $helper->redirect_location));
        return $helper;
    }

}

?>
