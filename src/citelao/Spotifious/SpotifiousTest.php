<?php

use PHPUnit\Framework\TestCase;
use Spotifious\Spotifious;
use OhAlfred\OhAlfred;

final class SpotifiousTest extends TestCase {
    public function testShowsSetupOnInitialStart() {
        $alfred = $this->createMock(OhAlfred::class);
        $spotifious = new Spotifious($alfred);

        $output = $spotifious->run('');
        $this->assertStringContainsStringIgnoringCase('Welcome', $output[0]['title']);
    }
}