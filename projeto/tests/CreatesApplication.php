<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Assert as PHPUnit;
use PHPUnit\Framework\ExpectationFailedException;

trait CreatesApplication
{
    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        Hash::driver('bcrypt')->setRounds(4);

        TestResponse::macro('assertSessionHasNoErrors', function ($keys = [], $format = null, $errorBag = 'default') {
            $bag = app('session.store')->get('errors');
            if (is_null($bag)) {
                PHPUnit::assertTrue(true);
                return $this;
            }

            $keys = (array) $keys;

            $errors = $bag->getBag($errorBag);

            foreach ($keys as $key => $value) {
                if (is_int($key)) {
                    PHPUnit::assertFalse($errors->has($value), "Session has error: $value");
                } else {
                    PHPUnit::assertNotContains($value, $errors->get($key, $format));
                }
            }

            return $this;
        });

        TestResponse::macro('assertSeeCount', function ($string, $occorrences = 1, $message = '') {
            $haystack = \mb_strtolower($this->getContent());
            $needle = \mb_strtolower($string);
            PHPUnit::assertEquals($occorrences, \mb_substr_count($haystack, $needle), $message);

            return $this;
        });

        TestResponse::macro('assertPatternCount', function ($pattern, $occorrences = 1, $message = '') {
            $haystack = \mb_strtolower($this->getContent());
            PHPUnit::assertEquals($occorrences, preg_match_all($pattern, $haystack), $message);

            return $this;
        });

        TestResponse::macro('assertDontSeeAll', function ($strings, $message = null) {
            try {
                foreach ($strings as $string) {
                    $this->assertDontSee($string);
                }
            } catch (ExpectationFailedException $e) {
                throw new ExpectationFailedException($message ?? $e->getMessage(), $e->getComparisonFailure());
            }
            return $this;
        });

        TestResponse::macro('assertSeeAll', function ($strings, $message = null) {
            try {
                foreach ($strings as $string) {
                    $this->assertSee($string);
                }
            } catch (ExpectationFailedException $e) {
                throw new ExpectationFailedException($message ?? $e->getMessage(), $e->getComparisonFailure());
            }
            return $this;
        });

        TestResponse::macro('assertSuccessfulOrRedirect', function() {
            PHPUnit::assertTrue(
                $this->isSuccessful() || $this->isRedirect(),
                'Response status code ['.$this->getStatusCode().'] is not a successful status code.'
            );
            return $this;
        });
        return $app;
    }
}
