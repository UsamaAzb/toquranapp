<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class BookingIntakePayloadRules
{
    public static function rules(): array
    {
        return [
            'parent_name' => ['required', 'string', 'max:255'],
            'parent_email' => ['nullable', 'email', 'max:255'],
            'parent_phone' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'children' => ['nullable', 'array', 'min:1'],
            'children.*.child_name' => ['required_with:children', 'string', 'max:255'],
            'children.*.child_age' => ['required_with:children', 'integer', 'min:3', 'max:18'],
            'children.*.child_grade' => ['required_with:children', 'integer', Rule::exists('grade_levels', 'id')],
            'children.*.school_system' => ['required_with:children', 'string', Rule::in(SchoolSystemOptions::values())],
            'children.*.service_interests' => ['required_with:children', 'array', 'min:1'],
            'children.*.service_interests.*' => ['required', 'string', 'max:255'],
            'child_name' => ['required_without:children', 'nullable', 'string', 'max:255'],
            'child_age' => ['required_without:children', 'nullable', 'integer', 'min:3', 'max:18'],
            'child_grade' => ['required_without:children', 'nullable', 'integer', Rule::exists('grade_levels', 'id')],
            'school_system' => ['required_without:children', 'nullable', 'string', Rule::in(SchoolSystemOptions::values())],
            'service_interests' => ['required_without:children', 'nullable', 'array', 'min:1'],
            'service_interests.*' => ['required', 'string', 'max:255'],
        ];
    }

    public static function messages(): array
    {
        return [
            'parent_name.required' => 'Parent name is required.',
            'parent_email.email' => 'Enter a valid parent email.',
            'children.*.child_name.required_with' => 'Child name is required.',
            'children.*.child_age.required_with' => 'Age is required.',
            'children.*.child_age.integer' => 'Age must be a whole number.',
            'children.*.child_age.min' => 'Age must be at least 3.',
            'children.*.child_age.max' => 'Age must be 18 or less.',
            'children.*.child_grade.required_with' => 'Grade is required.',
            'children.*.child_grade.exists' => 'Select a valid grade.',
            'children.*.school_system.required_with' => 'School system is required.',
            'children.*.school_system.in' => 'Select a valid school system.',
            'children.*.service_interests.required_with' => 'Select at least one service interest.',
            'children.*.service_interests.min' => 'Select at least one service interest.',
            'child_age.required_without' => 'Age is required.',
            'child_age.integer' => 'Age must be a whole number.',
            'child_age.min' => 'Age must be at least 3.',
            'child_age.max' => 'Age must be 18 or less.',
            'child_grade.required_without' => 'Grade is required.',
            'child_grade.exists' => 'Select a valid grade.',
            'school_system.required_without' => 'School system is required.',
            'school_system.in' => 'Select a valid school system.',
            'service_interests.required_without' => 'Select at least one service interest.',
            'service_interests.min' => 'Select at least one service interest.',
        ];
    }

    public static function attributes(): array
    {
        return [
            'parent_name' => 'parent name',
            'parent_email' => 'parent email',
            'parent_phone' => 'parent phone',
            'children.*.child_name' => 'child name',
            'children.*.child_age' => 'age',
            'children.*.child_grade' => 'grade',
            'children.*.school_system' => 'school system',
            'children.*.service_interests' => 'service interests',
            'child_name' => 'child name',
            'child_age' => 'age',
            'child_grade' => 'grade',
            'school_system' => 'school system',
            'service_interests' => 'service interests',
        ];
    }

    public static function applyAfter(Validator $validator, array $input): void
    {
        $validator->after(function (Validator $validator) use ($input): void {
            $children = $input['children'] ?? null;

            if (is_array($children) && count($children) === 0) {
                $validator->errors()->add('children', 'Add at least one child before saving intake.');
            }

            if (blank($input['parent_email'] ?? null) && blank($input['parent_phone'] ?? null)) {
                $validator->errors()->add('parent_email', 'Either parent email or parent phone is required.');
            }
        });
    }
}
