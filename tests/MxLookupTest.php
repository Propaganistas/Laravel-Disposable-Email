<?php

namespace Propaganistas\LaravelDisposableEmail {
    use Propaganistas\LaravelDisposableEmail\Tests\DnsMock;

    /**
     * Namespaced shadow of the global dns_get_record(). Because DisposableDomains
     * calls dns_get_record() unqualified from within this namespace, PHP resolves
     * to this function first, letting the tests drive the *default* resolver
     * (the one built in the constructor) deterministically - no real DNS.
     *
     * When the mock is inactive it transparently defers to the global function.
     */
    function dns_get_record($domain, $type)
    {
        if (DnsMock::$active) {
            return DnsMock::$return;
        }

        return \dns_get_record($domain, $type);
    }
}

namespace Propaganistas\LaravelDisposableEmail\Tests {

    use PHPUnit\Framework\Attributes\Test;

    /**
     * Controls the namespaced dns_get_record() shadow above.
     */
    class DnsMock
    {
        public static bool $active = false;

        public static mixed $return = [];
    }

    class MxLookupTest extends TestCase
    {
        /**
         * Build a fake MX resolver returning the given target hosts for any domain.
         */
        protected function resolver(array $targets)
        {
            return function ($domain) use ($targets) {
                return array_map(function ($target) {
                    return ['type' => 'MX', 'target' => $target, 'pri' => 10];
                }, $targets);
            };
        }

        #[Test]
        public function it_can_get_and_set_check_mx()
        {
            $this->assertFalse($this->disposable()->getCheckMx());

            $this->disposable()->setCheckMx(true);

            $this->assertTrue($this->disposable()->getCheckMx());
        }

        #[Test]
        public function it_defaults_the_mx_setting_to_off_and_reads_it_from_config()
        {
            // Ships disabled by default.
            $this->assertFalse($this->app['config']['disposable-email.mx.enabled']);
            $this->assertFalse($this->disposable()->getCheckMx());

            // Flipping the config prop rebuilds the singleton with MX checking on.
            $this->app['config']->set('disposable-email.mx.enabled', true);
            $this->app->forgetInstance('disposable_email.domains');

            $this->assertTrue($this->disposable()->getCheckMx());
        }

        #[Test]
        public function it_ignores_mx_records_when_disabled()
        {
            $this->disposable()
                ->setCheckMx(false)
                ->setMxResolver($this->resolver(['mail.mailinator.com']));

            // Front domain isn't listed and MX checking is off, so it passes.
            $this->assertFalse($this->disposable()->isDisposable('user@fresh-front-domain.example'));
        }

        #[Test]
        public function it_flags_a_domain_whose_mx_target_is_a_subdomain_of_a_listed_domain()
        {
            $this->disposable()
                ->setCheckMx(true)
                ->setMxResolver($this->resolver(['mail.mailinator.com.']));

            $this->assertTrue($this->disposable()->isDisposable('user@fresh-front-domain.example'));
        }

        #[Test]
        public function it_flags_a_domain_whose_mx_target_is_exactly_a_listed_domain()
        {
            $this->disposable()
                ->setCheckMx(true)
                ->setMxResolver($this->resolver(['yopmail.com']));

            $this->assertTrue($this->disposable()->isDisposable('user@fresh-front-domain.example'));
        }

        #[Test]
        public function it_lowercases_mx_targets_before_matching()
        {
            $this->disposable()
                ->setCheckMx(true)
                ->setMxResolver($this->resolver(['MAIL.MAILINATOR.COM.']));

            $this->assertTrue($this->disposable()->isDisposable('user@fresh-front-domain.example'));
        }

        #[Test]
        public function it_does_not_flag_a_domain_backed_by_a_legitimate_mail_provider()
        {
            $this->disposable()
                ->setCheckMx(true)
                ->setMxResolver($this->resolver(['gmail-smtp-in.l.google.com', 'alt1.gmail-smtp-in.l.google.com']));

            $this->assertFalse($this->disposable()->isDisposable('user@some-legit-company.example'));
        }

        #[Test]
        public function it_does_not_flag_when_no_mx_records_resolve()
        {
            $this->disposable()
                ->setCheckMx(true)
                ->setMxResolver($this->resolver([]));

            $this->assertFalse($this->disposable()->isDisposable('user@some-legit-company.example'));
        }

        #[Test]
        public function it_ignores_non_mx_records_returned_by_the_resolver()
        {
            $this->disposable()
                ->setCheckMx(true)
                ->setMxResolver(function ($domain) {
                    return [
                        ['type' => 'A', 'ip' => '203.0.113.10'],
                        ['type' => 'TXT', 'txt' => 'v=spf1 include:mailinator.com ~all'],
                    ];
                });

            $this->assertFalse($this->disposable()->isDisposable('user@some-legit-company.example'));
        }

        #[Test]
        public function it_ignores_mx_records_with_an_empty_or_missing_target()
        {
            $this->disposable()
                ->setCheckMx(true)
                ->setMxResolver(function ($domain) {
                    return [
                        ['type' => 'MX', 'target' => '', 'pri' => 10],
                        ['type' => 'MX', 'pri' => 20],
                    ];
                });

            $this->assertFalse($this->disposable()->isDisposable('user@some-legit-company.example'));
        }

        #[Test]
        public function it_still_matches_listed_domains_directly_without_resolving_mx()
        {
            $called = false;

            $this->disposable()
                ->setCheckMx(true)
                ->setMxResolver(function ($domain) use (&$called) {
                    $called = true;

                    return [];
                });

            // Listed domain short-circuits before any MX resolution happens.
            $this->assertTrue($this->disposable()->isDisposable('user@yopmail.com'));
            $this->assertFalse($called);
        }

        #[Test]
        public function it_memoizes_mx_lookups_per_domain()
        {
            $calls = 0;

            $this->disposable()
                ->setCheckMx(true)
                ->setMxResolver(function ($domain) use (&$calls) {
                    $calls++;

                    return [['type' => 'MX', 'target' => 'gmail-smtp-in.l.google.com', 'pri' => 5]];
                });

            $this->disposable()->isDisposable('a@repeated-domain.example');
            $this->disposable()->isDisposable('b@repeated-domain.example');

            // Same domain resolved twice hits the in-memory memo, so one lookup.
            $this->assertSame(1, $calls);
        }

        #[Test]
        public function it_refreshes_cached_lookups_when_the_resolver_is_replaced()
        {
            $this->disposable()
                ->setCheckMx(true)
                ->setMxResolver($this->resolver([]));

            $this->assertFalse($this->disposable()->isDisposable('user@swap-domain.example'));

            // Replacing the resolver must clear the memo so the new one is consulted.
            $this->disposable()->setMxResolver($this->resolver(['mail.mailinator.com']));

            $this->assertTrue($this->disposable()->isDisposable('user@swap-domain.example'));
        }

        #[Test]
        public function it_falls_back_to_the_default_dns_resolver_when_none_is_injected()
        {
            DnsMock::$active = true;

            try {
                // Records returned as-is (covers the non-false branch of the default resolver).
                DnsMock::$return = [['type' => 'MX', 'target' => 'mail.mailinator.com', 'pri' => 10]];

                // No setMxResolver() call: the constructor's dns_get_record() closure is used.
                $this->disposable()->setCheckMx(true);
                $this->assertTrue($this->disposable()->isDisposable('user@default-resolver.example'));

                // dns_get_record() returning false is normalised to [] (covers the false branch).
                DnsMock::$return = false;
                $this->assertFalse($this->disposable()->isDisposable('user@default-resolver-none.example'));
            } finally {
                DnsMock::$active = false;
                DnsMock::$return = [];
            }
        }

        #[Test]
        public function it_flags_mx_only_matches_through_the_indisposable_validation_rule()
        {
            $this->app['config']->set('disposable-email.mx.enabled', true);
            $this->app->forgetInstance('disposable_email.domains');

            $this->disposable()->setMxResolver(function ($domain) {
                return $domain === 'fresh-front-domain.example'
                    ? [['type' => 'MX', 'target' => 'mail.mailinator.com', 'pri' => 10]]
                    : [['type' => 'MX', 'target' => 'gmail-smtp-in.l.google.com', 'pri' => 5]];
            });

            $fails = $this->app['validator']->make(
                ['email' => 'user@fresh-front-domain.example'],
                ['email' => 'indisposable']
            );

            $passes = $this->app['validator']->make(
                ['email' => 'user@legit-company.example'],
                ['email' => 'indisposable']
            );

            $this->assertTrue($fails->fails());
            $this->assertTrue($passes->passes());
        }
    }
}
