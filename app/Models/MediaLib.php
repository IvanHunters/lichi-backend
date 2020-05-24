<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MediaLib extends Model
{
  protected $fillable = [
      'name','path', 'path_preview', 'creator_id','type'
  ];
}
