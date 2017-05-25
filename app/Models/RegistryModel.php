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
protected $hidden = ['uuid', 'entities_id', 'posix_uid','created_at', 'updated_at','orcid_id','registry_id'];

    public $incrementing = false;

    //one individual is registered


     public function individual()
    {
        return $this->hasOne('App\Models\Individuals', 'individuals_id', 'individuals_id');
    }

    //getting individuals by email 
    //
   
}