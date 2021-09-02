<?php

namespace Adsy2010\LaravelApiCredentials\Models;

use Adsy2010\LaravelApiCredentials\Exceptions\CredentialScopeAccessOutOfRangeException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Credential extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['key', 'value'];
    protected $guarded = ['id', 'value'];

    /**
     * @param string $service
     * @param $key
     * @param $value
     * @param array|null $scopes
     * @return Credential
     * @throws CredentialScopeAccessOutOfRangeException
     */
    public function store($key, $value, string $service = 'api', array $scopes = null): Credential
    {
        try {
            DB::beginTransaction();

            $this->key = $key;
            $this->value = encrypt($value);
            $this->service = $service;
            $this->save();

            foreach ($scopes as $scope) {
                //Set access variable to null if not present in array
                $access = $scope['access'] ?? null;

                //Check if access is set and its in scope access range
                if (isset($access) && !isset(array_flip(ScopeAccess::ALL)[$access])) {
                    throw new CredentialScopeAccessOutOfRangeException();
                }

                //Save the scope against the credential
                (new Scope)->store($this->id, $scope['name'], $access);
            }

            if (!isset($scopes) || empty($scopes)) {
                //The credential will be floating and unusable without a scope so we create an emergency public scope in read and write form
                (new Scope)->store($this->id, 'Public', ScopeAccess::READ_AND_WRITE);
            }

            DB::commit();
        }
        catch (CredentialScopeAccessOutOfRangeException $credentialScopeAccessOutOfRangeException) {
            DB::rollBack();
            throw $credentialScopeAccessOutOfRangeException;
        }

        return $this;
    }

    /**
     * @return HasMany
     */
    public function scopes(): HasMany
    {
        return $this->hasMany(Scope::class, 'credentials_id', 'id');
    }

    /**
     * @return bool|null
     */
    public function delete(): ?bool
    {
        $this->scopes()->delete();
        return parent::delete();
    }
}
