<?php

namespace Tests\Unit;

use App\Models\User;
use Tests\TestCase;

class UserCredentialSerializationTest extends TestCase
{
    public function test_recoverable_and_legacy_password_fields_are_hidden_from_serialization(): void
    {
        $user = new User([
            'name' => 'Support Target',
            'email' => 'support-target@example.test',
            'password' => 'hashed-value',
            'decryp_password' => 'legacy-secret',
            'recoverable_password_encrypted' => 'recoverable-secret',
        ]);

        $serialized = $user->toArray();

        $this->assertArrayNotHasKey('password', $serialized);
        $this->assertArrayNotHasKey('decryp_password', $serialized);
        $this->assertArrayNotHasKey('recoverable_password_encrypted', $serialized);
    }
}
