<?php

namespace Tests\Unit\Http\Requests;

use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Validator;
use Tests\CreatesApplication;
use Tests\Provisions\Http\Request\TestContainsOneOfEnumSearchRequest;

class ContainsOneOfEnumFilterRuleTest extends TestCase
{
    use CreatesApplication;

    public function testFilteringRules(): void
    {
        $rules = (new TestContainsOneOfEnumSearchRequest())->filteringRules();

        $expected = array(
            'filter.enum_field' => 'array',
            'filter.enum_field.contains' => 'array',
            'filter.enum_field.contains.*' => 'string|in:"first","second","third"',
            'filter.enum_field.notcontains' => 'array',
            'filter.enum_field.notcontains.*' => 'string|in:"first","second","third"',
        );

        $this->assertEquals($expected, $rules);
    }

    public function testValidationContainsOk(): void
    {
        $request = new TestContainsOneOfEnumSearchRequest();
        $data = [
            'filter' => ['enum_field' => ['contains' => ['first']]],
            'available_fields' => ['field']
        ];

        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->passes());
    }

    public function testValidationContainsFails(): void
    {
        $request = new TestContainsOneOfEnumSearchRequest();
        $data = [
            'filter' => [
                'enum_field' => [
                    'contains' => ['not_exists']
                ]
            ],
            'available_fields' => ['field']
        ];

        $validator = Validator::make($data, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
    }

    public function testValidationNotContainsOk(): void
    {
        $request = new TestContainsOneOfEnumSearchRequest();
        $data = [
            'filter' => ['enum_field' => ['notcontains' => ['first']]],
            'available_fields' => ['field']
        ];

        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->passes());
    }

    public function testValidationNotContainsFails(): void
    {
        $request = new TestContainsOneOfEnumSearchRequest();
        $data = [
            'filter' => [
                'enum_field' => [
                    'notcontains' => ['not_exists']
                ]
            ],
            'available_fields' => ['field']
        ];

        $validator = Validator::make($data, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
    }
}
