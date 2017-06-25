<?php

namespace ZapsterStudios\TeamPay\Models;

use Validator;
use Laravel\Cashier\Billable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class Team extends Model
{
    use Billable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'slug',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'braintree_id',
        'paypal_email',
        'card_brand',
        'card_last_four',
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('members', function (Builder $builder) {
            $builder->with('members');
        });
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Get all the team members.
     */
    public function members()
    {
        return $this->belongsToMany('App\User', 'team_members', 'team_id', 'user_id')
            ->withPivot('group', 'overwrites');
    }

    /**
     * Get all the team member fields.
     */
    public function teamMembers()
    {
        return $this->hasMany('ZapsterStudios\TeamPay\Models\TeamMember', 'team_id', 'id');
    }

    /**
     * Generate team slug.
     */
    static public function generateSlug($id, $slug, $current = false)
    {
        if($current && $current == $slug) {
            return $slug;
        }

        $unique = Validator::make(['slug' => $slug], [
            'slug' => 'required|unique:teams,slug'
        ]);

        return ($unique->fails() 
            ? self::generateSlug($id+1, $slug.'-'.$id, $current) 
            : $slug);
    }
}
