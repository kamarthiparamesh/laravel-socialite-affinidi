<?php

namespace Affinidi\SocialiteProvider;

class AffinidiSocialite
{
    public static function extend($socialite)
    {
        $socialite->extend('affinidi', function () use ($socialite) {
            $config = config('services.affinidi');
            return $socialite->buildProvider(AffinidiProvider::class, $config);
        });
    }
}
