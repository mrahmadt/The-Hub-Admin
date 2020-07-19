<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tab extends Model
{
    protected $fillable = [
        'name',
        'tabtype_id',
        'body',
        'url',
        'category_id',
        'policy_id',
        'itemorder',
    ];
    
    
    protected $dates = [
        'created_at',
        'updated_at',
    ];
    
    protected $appends = ['resource_url'];

    /* ************************ ACCESSOR ************************* */

    public function getResourceUrlAttribute()
    {
        return url('/admin/tabs/'.$this->getKey());
    }

    
    public function category()
    {
        return $this->belongsTo(Category::class); //'App\Models\Policy');
    }
    public function tabtype()
    {
        return $this->belongsTo(Tabtype::class); //'App\Models\Policy');
    }
}
