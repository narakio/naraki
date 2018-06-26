<?php namespace App\Models\Blog;

use App\Contracts\HasPermissions as HasPermissionsContract;
use App\Traits\Enumerable;
use App\Traits\Presentable;
use Illuminate\Database\Eloquent\Model;
use App\Contracts\Enumerable as EnumerableContract;
use App\Traits\Models\HasPermissions;

class BlogPost extends Model implements HasPermissionsContract, EnumerableContract
{
    use Presentable, Enumerable, HasPermissions;

    const PERMISSION_VIEW = 0b1;
    const PERMISSION_ADD = 0b10;
    const PERMISSION_EDIT = 0b100;
    const PERMISSION_DELETE = 0b1000;

//    public $timestamps = false;
    protected $primaryKey = 'blog_post_id';
    protected $fillable = [
        'user_id',
        'blog_post_status_id',
        'blog_post_title',
        'blog_post_slug',
        'blog_post_content',
        'blog_post_excerpt',
        'blog_post_sticky'
    ];
    protected $hidden = [
        'user_id',
        'blog_post_status_id'
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(
            function ($model) {
                $model->blog_post_slug = str_slug(
                    substr($model->blog_post_title,0,95),
                    '-',
                    app()->getLocale()
                );

                $latestSlug =
                    static::select(['blog_post_slug'])
                        ->whereRaw(sprintf(
                                'blog_post_slug = "%s" or blog_post_slug LIKE "%s-%%"',
                                $model->blog_post_slug,
                                $model->blog_post_slug)
                        )
                        ->latest($model->getKeyName())
                        ->value('blog_post_slug');
                if ($latestSlug) {
                    $pieces = explode('-', $latestSlug);

                    $number = intval(end($pieces));

                    $model->blog_post_slug .= sprintf('-%s', ($number + 1));
                }
            }
        );
    }

}