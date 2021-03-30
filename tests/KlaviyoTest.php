<?php

namespace Klaviyo;

use Klaviyo\Klaviyo;
use PHPUnit\Framework\TestCase;

class KlaviyoTest extends TestCase
{
    public $private_key = 'test_pk';
    public $public_key = 'test_public';

    function testGetPrivateKey() {
        $klaviyo = new Klaviyo($this->private_key, $this->public_key);
        $this->assertEquals($this->private_key, $klaviyo->getPrivateKey());
    }

    function testGetPublicKey() {
        $klaviyo = new Klaviyo($this->private_key, $this->public_key);
        $this->assertEquals($this->public_key, $klaviyo->getPublicKey());
    }

}
