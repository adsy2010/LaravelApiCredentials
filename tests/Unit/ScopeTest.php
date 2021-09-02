<?php

namespace Adsy2010\LaravelApiCredentials\Tests\Unit;

use Adsy2010\LaravelApiCredentials\Models\Credential;
use Adsy2010\LaravelApiCredentials\Models\Scope;
use Adsy2010\LaravelApiCredentials\Models\ScopeAccess;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScopeTest extends TestCase
{
    use DatabaseMigrations;
    use RefreshDatabase;

    public function testStoreWithNoScopes($service = 'TEST_SERVICE', $value = 'TEST_VALUE_STRING')
    {
        $key = 'PUBLIC';
        $scopes = [];

        $credentials = (new Credential)->store($key, $value, $service, $scopes);

        //Check the stored friendly key is the same as the one we set
        $isKeyTheSame = ($credentials->key === $key);

        //Check the stored encrypted value is not the same as the one we set
        $isValueDifferent = ($credentials->value === encrypt($value));

        //Check the decrypted values match
        $isDecryptedTheSame = (decrypt($credentials->value) === $value);

        $this->assertTrue($isKeyTheSame);
        $this->assertFalse($isValueDifferent);
        $this->assertTrue($isDecryptedTheSame);

        $this->assertDatabaseHas('credentials', ['key' => $key, 'service' => $service]);
        $this->assertDatabaseHas('scopes', ['credentials_id' =>  $credentials->id, 'name' => 'Public', 'access' => ScopeAccess::READ_AND_WRITE]);

        $credentials->loadCount('scopes');
        $this->assertEquals(1, $credentials->scopes_count);

        return $credentials;
    }

    public function testRetrieveCredentialValue()
    {
        $service = 'TEST_SERVICE';
        $value = 'TEST_VALUE_STRING';
        $credentials = $this->testStoreWithNoScopes($service, $value);

        $scopeValue = (new Scope)->retrieveCredentialValue($service, ['Public'], ScopeAccess::READ_AND_WRITE);

        $this->assertEquals($value, $scopeValue);
    }

    public function testStore()
    {
        $service = 'TEST_SERVICE';
        $value = 'TEST_VALUE_STRING';
        $credentials = $this->testStoreWithNoScopes($service, $value);

        $scope = (new Scope)->retrieve($service, ['Public'], ScopeAccess::READ_AND_WRITE);

        $this->assertNotEmpty($scope);

        $extraScope = (new Scope)->store($credentials->id, 'Extra', ScopeAccess::WRITE);

        $this->assertNotEmpty($extraScope);

        $this->assertDatabaseHas('scopes', ['name' => 'Extra', 'access' => ScopeAccess::WRITE]);
    }

    public function testRetrieve()
    {
        $service = 'TEST_SERVICE';
        $value = 'TEST_VALUE_STRING';
        $credentials = $this->testStoreWithNoScopes($service, $value);

        $scope = (new Scope)->retrieve($service, ['Public'], ScopeAccess::READ_AND_WRITE);

        $this->assertNotEmpty($scope);
    }
}
