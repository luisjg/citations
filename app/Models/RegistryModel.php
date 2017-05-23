<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistryModel extends Model
{
   
    protected $table = 'bedrock.registry';
    
    protected $primaryKey = "registry_id";

     /**
     * The attributes excluded from the model's JSON form.
     *
     * @var string
     */

    protected $hidden = ['uid', 'entities_id', 'posix_uid' 'orcid_id','created_at', 'updated_at'];

    public $incrementing = false;

     public function individual()
    {
        return $this->hasOne('App\Models\Individuals', 'individuals_id', 'individuals_id');
    }

    public function registryid($entities_id)
    {
        return $query->where('parent_entities_id', $entities_id)
                     ->where('role_position', '=' , 'member')
                     ->get();
    }


}