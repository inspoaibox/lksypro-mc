<?php

namespace Tests\Feature;

use App\Enums\ImagePermission;
use App\Models\Group;
use App\Models\Image;
use App\Models\Strategy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class LocalImageOutputTest extends TestCase
{
    use RefreshDatabase;

    public function test_local_image_can_be_served_without_public_symlink()
    {
        $publicRoot = public_path('i');
        if (is_link($publicRoot)) {
            @unlink($publicRoot);
        }
        if (! is_dir($publicRoot)) {
            mkdir($publicRoot, 0755, true);
        }

        $contents = 'local output fallback';
        $pathname = 'fallback.jpg';
        $storedFile = storage_path("app/uploads/{$pathname}");
        $this->beforeApplicationDestroyed(function () use ($publicRoot, $storedFile) {
            @unlink($storedFile);
            if (is_dir($publicRoot) && ! is_link($publicRoot)) {
                File::deleteDirectory($publicRoot);
            }
        });

        if (! is_dir(dirname($storedFile))) {
            mkdir(dirname($storedFile), 0755, true);
        }
        file_put_contents($storedFile, $contents);

        /** @var Strategy $strategy */
        $strategy = Strategy::query()->firstOrFail();
        $configs = $strategy->configs;
        $configs['url'] = config('app.url').'/i';
        $strategy->configs = $configs;
        $strategy->save();

        /** @var Group $group */
        $group = Group::query()->firstOrFail();

        $image = new Image();
        $image->forceFill([
            'group_id' => $group->id,
            'strategy_id' => $strategy->id,
            'path' => '',
            'name' => $pathname,
            'origin_name' => $pathname,
            'alias_name' => '',
            'size' => strlen($contents) / 1024,
            'mimetype' => 'image/jpeg',
            'extension' => 'jpg',
            'md5' => md5($contents),
            'sha1' => sha1($contents),
            'width' => 1,
            'height' => 1,
            'permission' => ImagePermission::Public,
            'is_unhealthy' => false,
            'uploaded_ip' => '127.0.0.1',
        ]);
        $image->save();

        $response = $this->get("/i/{$pathname}");

        $response->assertOk();
        $this->assertSame($contents, $response->streamedContent());
    }
}
