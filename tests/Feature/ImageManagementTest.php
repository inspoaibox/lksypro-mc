<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImageManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_image_rename_returns_not_found_for_missing_image()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put(route('user.images.rename'), [
            'id' => 999999,
            'name' => 'missing-image',
        ]);

        $response->assertStatus(404)
            ->assertJsonPath('status', false)
            ->assertJsonPath('message', '未找到该图片');
    }

    public function test_admin_image_delete_returns_not_found_for_missing_image()
    {
        $admin = User::factory()->create([
            'is_adminer' => true,
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.image.delete', 999999));

        $response->assertStatus(404)
            ->assertJsonPath('status', false)
            ->assertJsonPath('message', '未找到该图片');
    }
}
