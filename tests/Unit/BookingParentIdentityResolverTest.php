<?php

namespace Tests\Unit;

use App\Models\ParentModel;
use App\Services\BookingParentIdentityResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\Support\InteractsWithBookingIntakeTables;
use Tests\TestCase;

class BookingParentIdentityResolverTest extends TestCase
{
    use InteractsWithBookingIntakeTables;
    use RefreshDatabase;

    protected BookingParentIdentityResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createBookingIntakeTables();
        $this->resolver = app(BookingParentIdentityResolver::class);
    }

    public function test_parent_contact_resolution_uses_runtime_phone_normalizer_without_multi_phone_table(): void
    {
        Schema::dropIfExists('parent_phone_numbers');

        $parent = ParentModel::create([
            'first_name' => 'Mariam',
            'last_name' => 'Hany',
            'email' => 'mariam@example.test',
            'phone' => '+20 100 011 1222',
        ]);

        $result = $this->resolver->findParentByContacts(' MARIAM@example.test ', '01000111222');

        $this->assertFalse($result['multi_phone_available']);
        $this->assertSame('mariam@example.test', $result['normalized_email']);
        $this->assertSame('1000111222', $result['normalized_phone']);
        $this->assertSame($parent->id, $result['resolved_parent_id']);
        $this->assertNull($result['blocked_reason']);
    }

    public function test_parent_contact_resolution_ignores_partial_multi_phone_table_shape(): void
    {
        Schema::dropIfExists('parent_phone_numbers');
        Schema::create('parent_phone_numbers', function ($table): void {
            $table->id();
            $table->string('normalized_phone')->nullable();
        });

        $parent = ParentModel::create([
            'first_name' => 'Mariam',
            'last_name' => 'Hany',
            'email' => 'mariam@example.test',
            'phone' => '+20 100 011 1222',
        ]);

        $result = $this->resolver->findParentByContacts(null, '01000111222');

        $this->assertFalse($result['multi_phone_available']);
        $this->assertSame($parent->id, $result['resolved_parent_id']);
        $this->assertNull($result['blocked_reason']);
    }

    public function test_split_parent_contact_collision_blocks_resolution(): void
    {
        $emailParent = ParentModel::create([
            'email' => 'mariam@example.test',
            'phone' => '201000111222',
        ]);
        $phoneParent = ParentModel::create([
            'email' => 'other@example.test',
            'phone' => '201555666777',
        ]);

        $result = $this->resolver->findParentByContacts($emailParent->email, $phoneParent->phone);

        $this->assertNull($result['resolved_parent_id']);
        $this->assertSame([$emailParent->id, $phoneParent->id], $result['all_parent_ids']);
        $this->assertSame('Submitted email and phone match different parent accounts.', $result['blocked_reason']);
    }

    public function test_child_collision_summary_separates_duplicate_like_and_distinct_children(): void
    {
        $existingChild = $this->createExistingBookingChild();

        $duplicate = $this->resolver->childCollisionSummary(collect([
            [
                'child_name' => 'Youssef',
                'child_grade' => 6,
                'child_age' => 11,
                'school_system' => 'British',
            ],
        ]), $existingChild->booking_id);

        $distinct = $this->resolver->childCollisionSummary(collect([
            [
                'child_name' => 'Jana',
                'child_grade' => 3,
                'child_age' => 8,
                'school_system' => 'British',
            ],
        ]), $existingChild->booking_id);

        $ambiguous = $this->resolver->childCollisionSummary(collect([
            [
                'child_name' => 'Youssef',
                'child_grade' => 3,
                'child_age' => 8,
                'school_system' => 'Different School',
            ],
        ]), $existingChild->booking_id);

        $this->assertTrue($duplicate['has_duplicate_like']);
        $this->assertFalse($distinct['has_duplicate_like']);
        $this->assertFalse($distinct['has_ambiguous_same_name']);
        $this->assertTrue($ambiguous['has_ambiguous_same_name']);
    }

    public function test_linked_parent_contact_drift_requires_explicit_update_before_transfer(): void
    {
        $parent = ParentModel::create([
            'email' => 'mariam@example.test',
            'phone' => '201000111222',
        ]);

        $child = $this->createExistingBookingChild([
            'parent_id' => $parent->id,
            'parent_email' => 'mariam@example.test',
            'parent_phone' => '201555666777',
        ]);

        $this->assertSame($parent->id, $child->booking()->first()?->parent_id);

        $result = $this->resolver->resolveTransferTarget($child);

        $this->assertFalse($result['allowed']);
        $this->assertSame('update_linked_parent_contact', $result['outcome']);
        $this->assertSame($parent->id, $result['target_parent_id']);
        $this->assertSame('replace_phone', $result['contact_action']);
    }

    public function test_linked_parent_missing_phone_uses_booking_phone_without_blocking_transfer(): void
    {
        $parent = ParentModel::create([
            'email' => 'mariam@example.test',
            'phone' => null,
        ]);

        $child = $this->createExistingBookingChild([
            'parent_id' => $parent->id,
            'parent_email' => 'mariam@example.test',
            'parent_phone' => '201555666777',
        ]);

        $result = $this->resolver->resolveTransferTarget($child);

        $this->assertTrue($result['allowed']);
        $this->assertSame('use_linked_parent', $result['outcome']);
        $this->assertSame($parent->id, $result['target_parent_id']);
        $this->assertSame('none', $result['contact_action']);
        $this->assertSame('201555666777', $result['resolved_parent_phone']);
    }
}
