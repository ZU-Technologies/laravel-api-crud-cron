<?php

namespace App\Models;

use App\Events\UserCreatePost;
use App\Services\MentionProcessorService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Post extends Model
{
    use HasFactory, SoftDeletes;

    protected $with = ['author'];

    protected $fillable = [
        'body',
        'post_id',
        'deleted_at',
    ];

    protected $dispatchesEvents = [
        'created' => UserCreatePost::class,
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id')->withTrashed();
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function source()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }

    public function shares()
    {
        return $this->hasMany(Post::class);
    }

    public static function deletePost($id)
    {
        return Post::find($id)->delete();
    }

    public function likes()
    {
        return $this->hasMany(PostLike::class, 'post_id')->withTrashed();
    }

    public function likesWithoutTrashed()
    {
        return $this->hasMany(PostLike::class, 'post_id');
    }

    public function notifications()
    {
        return $this->morphMany(Notification::class, 'notifiable');
    }

    public function attachments()
    {
        return $this->hasMany(PostAttachment::class);
    }

    public function getLikeCountAttribute()
    {
        return $this->likesWithoutTrashed->count();
    }

    public function getCommentCountAttribute()
    {
        return $this->comments->count();
    }

    public function getLikedAttribute()
    {
        $user = Auth::user();

        return $this->likesWithoutTrashed->contains('user_id', $user->id);
    }

    public function getMentionablesAttribute()
    {
        $mentionProcessor = new MentionProcessorService(new User());
        $mentionProcessor->setContent($this->body);

        return $mentionProcessor->getMentionables();
    }
}
