<?php

namespace Tests\Unit;

use App\Services\CredentialService;
use PHPUnit\Framework\TestCase;

class CredentialServiceTest extends TestCase
{
    public function test_parent_password_uses_to_quran_prefix_and_normalized_name(): void
    {
        $service = new CredentialService;

        $this->assertSame('ToQuranAsmaa', $service->generateParentPasswordForName('asmaa'));
        $this->assertSame('ToQuranAbdElRahman', $service->generateParentPasswordForName('abd el rahman'));
    }

    public function test_parent_password_falls_back_to_generic_parent_when_name_is_blank(): void
    {
        $service = new CredentialService;

        $this->assertSame('ToQuranParent', $service->generateParentPasswordForName(null));
        $this->assertSame('ToQuranParent', $service->generateParentPasswordForName('   '));
    }

    public function test_child_password_defaults_to_to_quran(): void
    {
        $service = new CredentialService;

        $this->assertSame('ToQuran', $service->generateChildPassword());
    }
}
