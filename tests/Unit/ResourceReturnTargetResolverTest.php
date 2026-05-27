<?php

namespace Tests\Unit;

use App\Services\Library\ResourceReturnTargetResolver;
use Illuminate\Http\Request;
use Tests\TestCase;

class ResourceReturnTargetResolverTest extends TestCase
{
    public function test_it_prefers_safe_same_origin_return_to(): void
    {
        $request = Request::create('/resource?return_to=/student/classes/sessions/10');

        $target = app(ResourceReturnTargetResolver::class)->resolveFromRequest(
            $request,
            '/course/sat'
        );

        $this->assertSame(url('/student/classes/sessions/10'), $target);
    }

    public function test_it_rejects_cross_origin_return_to_and_uses_fallback(): void
    {
        $request = Request::create('/resource?return_to=https://evil.example/path');

        $target = app(ResourceReturnTargetResolver::class)->resolveFromRequest(
            $request,
            '/course/sat'
        );

        $this->assertSame(url('/course/sat'), $target);
    }
}
