<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{

    protected $table = "posts";

    public $timestamps = false;
  
    /**
     * Fillable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'imgUrl',
        'u_id',
        'url',
        'is_url',
        'title',
        'description',
        'price',
        'discount_price',
        'offer',
        'posted_time',
        'term',
        'category_id',
        'opening',
        'closing',
        'status',
        
    ];




    
}
