<?php

namespace App\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Model\User;
class Newinterest extends Model
{
    use HasFactory;

    protected $fillable = ['name'];


    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_interest', 'newinterest_id', 'user_id');
    }

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_interest', 'newinterest_id', 'post_id');
    }
}
