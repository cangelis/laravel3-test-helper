<?php
if (Request::cli()) {
    Autoloader::map(array(
        'TestHelper\TestHelper' => Bundle::path("testhelper") . "testhelper.php",
    ));
    Bundle::start("testhelper");
}
?>