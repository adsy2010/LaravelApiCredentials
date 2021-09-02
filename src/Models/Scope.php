<?php

namespace Adsy2010\LaravelApiCredentials\Models;

use Adsy2010\LaravelApiCredentials\Exceptions\CredentialUnavailableException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Scope extends Model
{
    use HasFactory;

    protected $guarded = ['id', 'credentials_id'];
    protected $fillable = ['name', 'access'];

    /**
     * @param $credential
     * @param $name
     * @param int $access uses ScopeAccess constants READ, WRITE, READ_AND_WRITE
     * @return $this
     */
    public function store($credential, $name,  int $access = ScopeAccess::READ): Scope
    {
        $this->credentials_id = $credential;
        $this->name = $name;
        $this->access = $access;
        $this->save();

        return $this;
    }

    /**
     * @param string $service the service the credentials relate to
     * @param array $scopes an array of names of scopes
     * @param int $access uses ScopeAccess constants READ, WRITE, READ_AND_WRITE
     * @return Builder[]|Collection
     */
    public function retrieve(string $service, array $scopes, int $access = ScopeAccess::READ)
    {
        return Scope::with('credentials')
            ->whereHas('credentials', function($query) use ($service) {
                $query->where('service', '=', $service);
            })
            ->whereIn('name', $scopes)
            ->where('access', '=', $access)
            ->get();
    }

    /**
     * @param string $service the service the credentials relate to
     * @param array $scopes an array of names of scopes
     * @param int $access uses ScopeAccess constants READ, WRITE, READ_AND_WRITE
     * @return mixed
     * @throws CredentialUnavailableException
     */
    public function retrieveCredentialValue(string $service, array $scopes, int $access = ScopeAccess::READ)
    {
        $scopes = self::retrieve($service, $scopes, $access);

        if(!(count($scopes) > 0)) {
            throw new CredentialUnavailableException();
        }

        $credential = $scopes->first()->credentials;

        return decrypt($credential->value);
    }

    public function credentials()
    {
        return $this->belongsTo(Credential::class);
    }
}
