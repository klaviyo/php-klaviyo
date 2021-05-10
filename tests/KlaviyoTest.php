<?php

declare(strict_types=1);

namespace Klaviyo;

use PHPUnit\Framework\TestCase;

class KlaviyoTest extends TestCase
{
    public string $private_key = 'test_pk';
    public string $public_key = 'test_public';

    public function test_creating_klaviyo_class()
    {
        $klaviyo = new Klaviyo($this->private_key, $this->public_key);
        self::assertInstanceOf(Klaviyo::class, $klaviyo);
    }

    public function test_getting_lists()
    {
        $klaviyo = new Klaviyo($this->private_key, $this->public_key);
        self::assertInstanceOf(Lists::class, $klaviyo->lists());
    }

    public function test_getting_metrics()
    {
        $klaviyo = new Klaviyo($this->private_key, $this->public_key);
        self::assertInstanceOf(Metrics::class, $klaviyo->metrics());
    }

    public function test_getting_profiles()
    {
        $klaviyo = new Klaviyo($this->private_key, $this->public_key);
        self::assertInstanceOf(Profiles::class, $klaviyo->profiles());
    }

    public function test_getting_public_api()
    {
        $klaviyo = new Klaviyo($this->private_key, $this->public_key);
        self::assertInstanceOf(PublicAPI::class, $klaviyo->publicAPI());
    }
}
