<?php

declare(strict_types=1);

namespace Tests\Unit\Validations;

use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Tests\CreatesApplication;

class ValidationsTest extends TestCase
{
    use CreatesApplication;

    public function testUuidOrHexValidation()
    {
        $data = [
            null,
            1111,
            'test',
            false,
            Str::uuid()->toString(),
            Str::uuid()->getHex()->toString(),
        ];

        $validation = Validator::make($data, ['*' => 'uuid_or_hex']);
        $this->assertCount(4, $validation->errors());

        $this->assertEquals(
            [
                ['The 0 field is invalid. Please provide a valid uuid or hex.'],
                ['The 1 field is invalid. Please provide a valid uuid or hex.'],
                ['The 2 field is invalid. Please provide a valid uuid or hex.'],
                ['The 3 field is invalid. Please provide a valid uuid or hex.'],
            ],
            $validation->errors()->toArray()
        );
    }

    public function testIcdCode()
    {
        $data = [
            'icd_codes' => [
                'asdsad',
                11233,
                null,
                false,
                '%sasd',
                'I77/000',
                'I77.0000',
                'I77.00000',
                'I7700000',
                'E1151',
                'I70203',
            ]
        ];

        $validation = Validator::make($data, ['icd_codes.*' => 'icd_code']);
        $this->assertCount(7, $validation->errors());
        $this->assertArrayHasKey('icd_codes.1', $validation->errors()->toArray());
        $this->assertArrayHasKey('icd_codes.2', $validation->errors()->toArray());
        $this->assertArrayHasKey('icd_codes.3', $validation->errors()->toArray());
        $this->assertArrayHasKey('icd_codes.4', $validation->errors()->toArray());
        $this->assertArrayHasKey('icd_codes.5', $validation->errors()->toArray());
        $this->assertArrayHasKey('icd_codes.7', $validation->errors()->toArray());
        $this->assertArrayHasKey('icd_codes.8', $validation->errors()->toArray());
    }
}