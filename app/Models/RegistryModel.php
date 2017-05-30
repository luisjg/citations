<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistryModel extends Model
{
   
    public $table = 'bedrock.registry';
    
    public $primaryKey = "registry_id";

     /**
     * The attributes excluded from the model's JSON form.
     *
     * @var string
     */
    public $hidden = ['uuid', 'entities_id', 'posix_uid','created_at', 'updated_at','orcid_id','registry_id'];

    public $incrementing = false;

    //one individual is registered

   public function individual()
    {
        return $this->hasOne('App\Models\IndividualModel','individuals_id','entities_id');

    }

    public function membership()
    {
        return $this->hasMany('App\Models\MembershipModel','individuals_id','entities_id');

    }
   
}