<?php

use PHPUnit\Framework\TestCase;
include 'src/Klaviyo.php';

class Test_Stack extends TestCase
{
    var $test_instance;

    public function test_track()
    {
        $a = "This is a test string, ";
        $a .= "Not sure if this will work";

        print $a;

        self::assertEquals("This is a test string, Not sure if this will work", $a);
    }
}