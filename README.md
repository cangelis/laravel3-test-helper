# Laravel3 Unit Test Helper Bundle

This bundle is a helper to run controller tests easily.

## Installation

```php artisan bundle:install testhelper```

## Usage

```php
TestHelper \TestHelper\TestHelper::runControllerTest(string controllerName, string methodName, string requestMethod, array postParams, array getParams);
```

**controllerName**: Name of the controller to be tested.

**methodName**: Name of the controller method.

**requestMethod**: HTTP request method. eg: POST,GET

**postParams**: POST Parameters as an array. eg: array('name' => 'Can')

**getParams**: Parameters for controller method.

**return**: TestHelper object.

### TestHelper object

After running controller test, it returns a TestHelper which has several properties that you can use in your test.

**statusCode**: HTTP Status response code (200, 302, 404 etc)

**view**: View name of the response (if response is not redirect)

**errors**: Error messages from Validator class of Laravel

**data**: Data that are sent to the view

**redirect_location**: Redirect Location as string (eg: http://www.google.com)

**redirect_params**: Redirect params as array. eg: if redirect is ***auth/login/1***, ***$result->redirect_params = array('auth','login','1')***

## An example:

This example uses [Simple Validator Bundle](https://github.com/cangelis/simple-validator-laravel) due to its testability

```php
use \TestHelper\TestHelper as TestHelper;

class LoginTest extends PHPUnit_Framework_TestCase {

    public function testSuccessfulAdd() {
            $test_data = array('title' => 'test title', 'content' => 'test content');
            // login user, $this->user_id is predefined in somewhere don't care
            Session::put("user_id", $this->user_id);
            // call post/new
            $test_result = TestHelper::runControllerTest("post", "new", "POST", $test_data);
            // get validation object which sent via ->with('validation', serialize($validation))
            $validation = unserialize(Session::get('validation'));
            
            /*
             * $test_result->redirect_params = array(
             * 'post',
             * 'update'
             * '{post_id}'
             * ) = /post/update/{post_id}
             *
            */
            $this->assertFalse($validation);
            // is this a redirect ?
            $this->assertEquals(302, $test_result->statusCode);
            // check whether redirect url is correct or not
            $this->assertEquals("post", $test_result->redirect_params[0]);
            $this->assertEquals("update", $test_result->redirect_params[1]);
            $post = Post::find($test_result->redirect_params[2]);
            // post should be added
            $this->assertNotNull($post);

            // We are redirecting to: post/update/{id}
            $test2 = TestHelper::runControllerTest($test_result->redirect_params[0], $test_result->redirect_params[1], "GET", null, array($test_result->redirect_params[2]));
            $this->assertTrue(Session::get('new_post'));
            // check whether data sent to view are correct.
            $this->assertEquals($test_data['title'], $test2->data['post']->title);
            $this->assertEquals($test_data['content'], $test2->data['post']->content);
            $this->assertEquals($this->user_id, $test2->data['post']->user_id);
            $post->delete();
        }

        public function testEmptyContentInput() {
            $test_data = array('title' => 'test title');
            // login user
            Session::put("user_id", $this->user_id);
            $test_result = TestHelper::runControllerTest("post", "new", "POST", $test_data);
            $validation = unserialize(Session::get('validation'));

            $this->assertEquals(302, $test_result->statusCode);
            $this->assertEquals("post", $test_result->redirect_params[0]);
            $this->assertEquals("new", $test_result->redirect_params[1]);
            $this->assertFalse($validation->isSuccess());
            $this->assertTrue($validation->has('content', 'required'));
            $this->assertFalse($validation->has('title', 'required'));
            $this->assertFalse($validation->has('auth', 'is_logged_in'));
        }
}
```

You can check out the complete example project which uses TestHelper on https://github.com/cangelis/laravel-test-helper-example
