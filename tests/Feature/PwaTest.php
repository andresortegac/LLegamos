<?php
namespace Tests\Feature;
use Tests\TestCase;
class PwaTest extends TestCase
{
    public function test_public_pages_expose_installable_pwa_metadata(): void
    {
        $this->get('/')->assertOk()->assertSee('/manifest.webmanifest', false)->assertSee('/sw.js', false);
        $manifest = json_decode(file_get_contents(public_path('manifest.webmanifest')), true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('Llegamos', $manifest['name']);
        $this->assertSame('standalone', $manifest['display']);
        $this->assertFileExists(public_path('sw.js'));
        $this->assertFileExists(public_path('offline.html'));
        $this->assertFileExists(public_path('icons/icon-192.png'));
        $this->assertFileExists(public_path('icons/icon-512.png'));
    }

    public function test_service_worker_does_not_cache_private_navigation_responses(): void
    {
        $worker = file_get_contents(public_path('sw.js'));
        $this->assertStringContainsString("request.mode === 'navigate'", $worker);
        $this->assertStringContainsString("fetch(request).catch", $worker);
        $this->assertStringNotContainsString("cache.put(request", substr($worker, strpos($worker, "request.mode === 'navigate'"), 220));
    }
}
