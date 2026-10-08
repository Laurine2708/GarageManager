<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Fortify\Features;

/** Base commune aux tests : fournit les utilitaires transverses du banc de test. */
abstract class TestCase extends BaseTestCase
{
    /** Marque un scénario comme ignoré si une fonction Fortify requise est inactive. */
    protected function skipUnlessFortifyHas(string $feature, ?string $message = null): void
    {
        if (! Features::enabled($feature)) {
            $this->markTestSkipped($message ?? "Fortify feature [{$feature}] is not enabled.");
        }
    }
}
