<?php

namespace Adsy2010\LaravelApiCredentials\Tests\Unit;

use Adsy2010\LaravelApiCredentials\Models\Credential;
use Adsy2010\LaravelApiCredentials\Models\Scope;
use Adsy2010\LaravelApiCredentials\Models\ScopeAccess;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CredentialTest extends TestCase
{

    use DatabaseMigrations;
    use RefreshDatabase;

    public function testStore()
    {
        $this->testStoreWithNoScopes();
        $this->testStoreWithOneScope();
        $this->testStoreWithMultipleScopes();
    }

    public function testStoreWithNoScopes()
    {
        $key = 'PUBLIC';
        $value = 'TEST_VALUE_STRING';
        $service = 'TEST_SERVICE';
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

    public function testStoreWithOneScope()
    {
        $key = 'PUBLIC';
        $value = 'TEST_VALUE_STRING';
        $service = 'TEST_SERVICE';
        $scopes = [['name' => 'My scope', 'access' => ScopeAccess::READ]];

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
        $this->assertDatabaseHas('scopes', ['credentials_id' =>  $credentials->id, 'name' => 'My scope', 'access' => ScopeAccess::READ]);

        $credentials->loadCount('scopes');
        $this->assertEquals(1, $credentials->scopes_count);
    }

    public function testStoreWithMultipleScopes()
    {
        $key = 'PUBLIC';
        $value = 'TEST_VALUE_STRING';
        $service = 'TEST_SERVICE';
        $scopes = [
            ['name' => 'My scope', 'access' => ScopeAccess::READ],
            ['name' => 'My second scope', 'access' => ScopeAccess::WRITE]
        ];

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
        $this->assertDatabaseHas('scopes', ['credentials_id' =>  $credentials->id, 'name' => 'My scope', 'access' => ScopeAccess::READ]);
        $this->assertDatabaseHas('scopes', ['credentials_id' =>  $credentials->id, 'name' => 'My second scope', 'access' => ScopeAccess::WRITE]);

        $credentials->loadCount('scopes');
        $this->assertEquals(2, $credentials->scopes_count);
    }

    public function testDelete()
    {
        $credentials = $this->testStoreWithNoScopes();

        $this->assertEquals(1, $credentials->scopes_count);
        $this->assertNotEmpty($credentials->scopes);

        $credentials->delete();

        $credentials->refresh();

        $this->assertEmpty($credentials->scopes);

        $this->assertSoftDeleted($credentials);
    }
}
