<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    protected $fillable = [
        'name',
        'url',
        'icon',
        'description',
        'isNewPage',
        'isNewPageForIframe',
        'activated',
        'isFeatured',
        'category_id',
    
    ];
    
    
    protected $dates = [
        'created_at',
        'updated_at',
    
    ];
    
    protected $appends = ['resource_url'];

    /* ************************ ACCESSOR ************************* */

    public function getResourceUrlAttribute()
    {
        return url('/admin/applications/'.$this->getKey());
    }
    public function category()
    {
        return $this->belongsTo(Category::class); //'App\Models\Policy');
    }
}
