<?php

namespace VeriDogrula\Tests;

use PHPUnit\Framework\TestCase;
use VeriDogrula\Validator;

class ValidatorTest extends TestCase
{
    private Validator $validator;

    protected function setUp(): void
    {
        $this->validator = new Validator();
    }

    public function test_required_rule(): void
    {
        $this->assertTrue($this->validator->validate(
            ['email' => 'test@example.com'],
            ['email' => 'required']
        ));

        $this->assertFalse($this->validator->validate(
            ['email' => ''],
            ['email' => 'required']
        ));
    }

    public function test_email_rule(): void
    {
        $this->assertTrue($this->validator->validate(
            ['email' => 'test@example.com'],
            ['email' => 'email']
        ));

        $this->assertFalse($this->validator->validate(
            ['email' => 'invalid-email'],
            ['email' => 'email']
        ));
    }

    public function test_url_rule(): void
    {
        $this->assertTrue($this->validator->validate(
            ['website' => 'https://example.com'],
            ['website' => 'url']
        ));

        $this->assertFalse($this->validator->validate(
            ['website' => 'not-a-url'],
            ['website' => 'url']
        ));
    }

    public function test_integer_rule(): void
    {
        $this->assertTrue($this->validator->validate(
            ['age' => 25],
            ['age' => 'integer']
        ));

        $this->assertFalse($this->validator->validate(
            ['age' => '25.5'],
            ['age' => 'integer']
        ));
    }

    public function test_numeric_rule(): void
    {
        $this->assertTrue($this->validator->validate(
            ['price' => '19.99'],
            ['price' => 'numeric']
        ));

        $this->assertFalse($this->validator->validate(
            ['price' => 'abc'],
            ['price' => 'numeric']
        ));
    }

    public function test_min_rule(): void
    {
        $this->assertTrue($this->validator->validate(
            ['password' => 'MyPassword123'],
            ['password' => 'min:8']
        ));

        $this->assertFalse($this->validator->validate(
            ['password' => 'short'],
            ['password' => 'min:8']
        ));
    }

    public function test_max_rule(): void
    {
        $this->assertTrue($this->validator->validate(
            ['username' => 'user'],
            ['username' => 'max:20']
        ));

        $this->assertFalse($this->validator->validate(
            ['username' => 'this_is_a_very_long_username_that_exceeds_limit'],
            ['username' => 'max:20']
        ));
    }

    public function test_min_value_rule(): void
    {
        $this->assertTrue($this->validator->validate(
            ['age' => 25],
            ['age' => 'min_value:18']
        ));

        $this->assertFalse($this->validator->validate(
            ['age' => 15],
            ['age' => 'min_value:18']
        ));
    }

    public function test_max_value_rule(): void
    {
        $this->assertTrue($this->validator->validate(
            ['age' => 65],
            ['age' => 'max_value:100']
        ));

        $this->assertFalse($this->validator->validate(
            ['age' => 150],
            ['age' => 'max_value:100']
        ));
    }

    public function test_phone_rule(): void
    {
        $this->assertTrue($this->validator->validate(
            ['phone' => '+905551234567'],
            ['phone' => 'phone:TR']
        ));

        $this->assertFalse($this->validator->validate(
            ['phone' => 'invalid'],
            ['phone' => 'phone:TR']
        ));
    }

    public function test_password_rule(): void
    {
        $this->assertTrue($this->validator->validate(
            ['password' => 'SecurePass123!'],
            ['password' => 'password']
        ));

        $this->assertFalse($this->validator->validate(
            ['password' => 'weak'],
            ['password' => 'password']
        ));
    }

    public function test_matches_rule(): void
    {
        $this->assertTrue($this->validator->validate(
            ['password' => 'pass123', 'password_confirm' => 'pass123'],
            ['password_confirm' => 'matches:password']
        ));

        $this->assertFalse($this->validator->validate(
            ['password' => 'pass123', 'password_confirm' => 'pass456'],
            ['password_confirm' => 'matches:password']
        ));
    }

    public function test_in_rule(): void
    {
        $this->assertTrue($this->validator->validate(
            ['status' => 'active'],
            ['status' => 'in:active,inactive,pending']
        ));

        $this->assertFalse($this->validator->validate(
            ['status' => 'deleted'],
            ['status' => 'in:active,inactive,pending']
        ));
    }

    public function test_not_in_rule(): void
    {
        $this->assertTrue($this->validator->validate(
            ['status' => 'active'],
            ['status' => 'not_in:deleted,banned']
        ));

        $this->assertFalse($this->validator->validate(
            ['status' => 'banned'],
            ['status' => 'not_in:deleted,banned']
        ));
    }

    public function test_accepted_rule(): void
    {
        $this->assertTrue($this->validator->validate(
            ['terms' => 'on'],
            ['terms' => 'accepted']
        ));

        $this->assertFalse($this->validator->validate(
            ['terms' => 'off'],
            ['terms' => 'accepted']
        ));
    }

    public function test_array_rule(): void
    {
        $this->assertTrue($this->validator->validate(
            ['tags' => ['tag1', 'tag2']],
            ['tags' => 'array']
        ));

        $this->assertFalse($this->validator->validate(
            ['tags' => 'not-an-array'],
            ['tags' => 'array']
        ));
    }

    public function test_ipv4_rule(): void
    {
        $this->assertTrue($this->validator->validate(
            ['ip' => '192.168.1.1'],
            ['ip' => 'ipv4']
        ));

        $this->assertFalse($this->validator->validate(
            ['ip' => 'not-an-ip'],
            ['ip' => 'ipv4']
        ));
    }

    public function test_slug_rule(): void
    {
        $this->assertTrue($this->validator->validate(
            ['slug' => 'my-awesome-slug'],
            ['slug' => 'slug']
        ));

        $this->assertFalse($this->validator->validate(
            ['slug' => 'My Awesome Slug!'],
            ['slug' => 'slug']
        ));
    }

    public function test_alpha_rule(): void
    {
        $this->assertTrue($this->validator->validate(
            ['name' => 'John'],
            ['name' => 'alpha']
        ));

        $this->assertFalse($this->validator->validate(
            ['name' => 'John123'],
            ['name' => 'alpha']
        ));
    }

    public function test_alpha_num_rule(): void
    {
        $this->assertTrue($this->validator->validate(
            ['code' => 'ABC123'],
            ['code' => 'alpha_num']
        ));

        $this->assertFalse($this->validator->validate(
            ['code' => 'ABC-123'],
            ['code' => 'alpha_num']
        ));
    }

    public function test_alpha_dash_rule(): void
    {
        $this->assertTrue($this->validator->validate(
            ['username' => 'user-name_123'],
            ['username' => 'alpha_dash']
        ));

        $this->assertFalse($this->validator->validate(
            ['username' => 'user@name'],
            ['username' => 'alpha_dash']
        ));
    }

    public function test_multiple_rules(): void
    {
        $this->assertTrue($this->validator->validate(
            ['email' => 'test@example.com', 'password' => 'SecurePass123!'],
            [
                'email' => 'required|email',
                'password' => 'required|min:8|password'
            ]
        ));

        $this->assertFalse($this->validator->validate(
            ['email' => 'invalid', 'password' => 'weak'],
            [
                'email' => 'required|email',
                'password' => 'required|min:8|password'
            ]
        ));
    }

    public function test_get_errors(): void
    {
        $this->validator->validate(
            ['email' => 'invalid'],
            ['email' => 'email']
        );

        $errors = $this->validator->getErrors();
        $this->assertIsArray($errors);
        $this->assertArrayHasKey('email', $errors);
    }

    public function test_get_validated_data(): void
    {
        $this->validator->validate(
            ['email' => 'test@example.com'],
            ['email' => 'email']
        );

        $data = $this->validator->getValidatedData();
        $this->assertArrayHasKey('email', $data);
        $this->assertEquals('test@example.com', $data['email']);
    }

    public function test_custom_rule(): void
    {
        $this->validator->addRule('custom', function($value) {
            return strlen($value) > 5;
        });

        $this->assertTrue($this->validator->validate(
            ['field' => 'longvalue'],
            ['field' => 'custom']
        ));

        $this->assertFalse($this->validator->validate(
            ['field' => 'short'],
            ['field' => 'custom']
        ));
    }

    public function test_reset(): void
    {
        $this->validator->validate(
            ['email' => 'invalid'],
            ['email' => 'email']
        );

        $this->assertNotEmpty($this->validator->getErrors());

        $this->validator->reset();
        $this->assertEmpty($this->validator->getErrors());
    }

    public function test_language_switching(): void
    {
        $this->validator->setLanguage('en');
        $this->validator->validate(
            ['email' => 'invalid'],
            ['email' => 'email']
        );

        $errors = $this->validator->getErrors();
        $this->assertStringContainsString('email', strtolower($errors['email']));
    }

    public function test_zero_value(): void
    {
        $this->assertTrue($this->validator->validate(
            ['count' => '0'],
            ['count' => 'required']
        ));
    }
}
