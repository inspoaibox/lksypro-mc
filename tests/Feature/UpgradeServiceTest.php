<?php

namespace Tests\Feature;

use App\Services\UpgradeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class UpgradeServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_progress_returns_cached_upgrade_progress()
    {
        $progress = [
            'status' => 'installing',
            'message' => '准备升级...',
        ];
        Cache::put('upgrade_progress', $progress);

        $this->assertSame($progress, (new UpgradeService('V 2.1'))->getProgress());
    }
}
