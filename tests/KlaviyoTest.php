<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Klaviyo\Klaviyo;

class KlaviyoTest extends TestCase
{
    public $private_key = 'test_pk';
    public $public_key = 'test_public';

    public function test_creating_klaviyo_class()
    {
        $klaviyo = new Klaviyo($this->private_key, $this->public_key);
        self::assertInstanceOf(Klaviyo::class, $klaviyo);
    }

    public function test_getting_lists()
    {
        $klaviyo = new Klaviyo($this->private_key, $this->public_key);
        self::assertInstanceOf(\Klaviyo\Lists::class, $klaviyo->lists());
    }

    public function test_getting_metrics()
    {
        $klaviyo = new Klaviyo($this->private_key, $this->public_key);
        self::assertInstanceOf(\Klaviyo\Metrics::class, $klaviyo->metrics());
    }

    public function test_getting_profiles()
    {
        $klaviyo = new Klaviyo($this->private_key, $this->public_key);
        self::assertInstanceOf(\Klaviyo\Profiles::class, $klaviyo->profiles());
    }

    public function test_getting_public_api()
    {
        $klaviyo = new Klaviyo($this->private_key, $this->public_key);
        self::assertInstanceOf(\Klaviyo\PublicAPI::class, $klaviyo->publicAPI());
    }
}
