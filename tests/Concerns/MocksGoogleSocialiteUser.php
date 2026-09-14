<?php

namespace Tests\Concerns;

use Laravel\Socialite\Contracts\User as SocialiteUser;
use Mockery;
use Mockery\MockInterface;

trait MocksGoogleSocialiteUser
{
    protected function makeGoogleSocialiteUser(array $attributes = []): SocialiteUser|MockInterface
    {
        $user = Mockery::mock(SocialiteUser::class);
        $user->shouldReceive('getId')->andReturn($attributes['id'] ?? 'google-user-123');
        $user->shouldReceive('getEmail')->andReturn($attributes['email'] ?? 'guest@gmail.com');
        $user->shouldReceive('getName')->andReturn($attributes['name'] ?? 'Google Guest');
        $user->shouldReceive('getAvatar')->andReturn($attributes['avatar'] ?? 'https://example.com/avatar.jpg');

        return $user;
    }
}
