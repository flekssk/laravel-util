<?php

namespace Tests\Unit\Http\Requests;

use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\In;
use Tests\CreatesApplication;
use Tests\Provisions\Http\Request\TestOneOfEnumSearchRequest;

class OneOfEnumFilterRuleTest extends TestCase
{
    use CreatesApplication;

    public function testFilteringRules(): void
    {
        $rules = (new TestOneOfEnumSearchRequest())->filteringRules();
        $expected =
            array (
                'filter.enum_field' =>
                    array (
                        0 => 'string',
                        1 => new In(['first', 'second','third'])
                    ),
            );

        $this->assertEquals($expected, $rules);
    }

    public function testValidationOk(): void
    {
        $request = new TestOneOfEnumSearchRequest();
        $data = [
            'filter' => ['enum_field' => 'first'],
            'available_fields' => ['test_field']
        ];

//        $request->query->replace($data);
        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->passes());
    }

    public function testValidationFails(): void
    {
        $request = new TestOneOfEnumSearchRequest();
        $data = [
            'filter' => ['enum_field' => 'nonexistent'],
            'available_fields' => ['test_field']
        ];

        $request->query->replace($data);
        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->fails());
    }
}
