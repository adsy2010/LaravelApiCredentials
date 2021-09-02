<?php

namespace Adsy2010\LaravelApiCredentials\Models;

use Adsy2010\LaravelApiCredentials\Exceptions\CredentialUnavailableException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Scope extends Model
{
    use HasFactory;

    protected $guarded = ['id', 'credentials_id'];
    protected $fillable = ['name', 'access'];

    /**
     * @param int $credential
     * @param string $name
     * @param int $access uses ScopeAccess constants READ, WRITE, READ_AND_WRITE
     * @return $this
     */
    public function store(int $credential, string $name,  int $access = ScopeAccess::READ): Scope
    {
        if($access === null) {
            $access = ScopeAccess::READ;
        }

        $this->credentials_id = $credential;
        $this->name = $name;
        $this->access = $access;
        $this->save();

        return $this;
    }

    /**
     * @param string $service the service the credentials relate to
     * @param string $scope
     * @param int $access uses ScopeAccess constants READ, WRITE, READ_AND_WRITE
     * @return Builder[]|Collection
     */
    public function retrieve(string $service, string $scope, int $access = ScopeAccess::READ)
    {
        return Scope::with('credentials')
            ->whereHas('credentials', function($query) use ($service) {
                $query->where('service', '=', $service);
            })
            ->where('name', '=', $scope)
            ->where('access', '=', $access)
            ->get();
    }

    /**
     * @param string $service the service the credentials relate to
     * @param string $scope
     * @param int $access uses ScopeAccess constants READ, WRITE, READ_AND_WRITE
     * @return mixed
     * @throws CredentialUnavailableException
     */
    public function retrieveCredentialValue(string $service, string $scope, int $access = ScopeAccess::READ)
    {
        $scopes = self::retrieve($service, $scope, $access);

        if(!(count($scopes) > 0)) {
            throw new CredentialUnavailableException();
        }

        $credential = $scopes->first()->credentials;

        return decrypt($credential->value);
    }

    /**
     * @return BelongsTo
     */
    public function credentials(): BelongsTo
    {
        return $this->belongsTo(Credential::class);
    }
}
