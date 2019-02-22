<?php namespace App\Models\Blog;

use App\Contracts\Enumerable as EnumerableContract;
use App\Contracts\HasAnEntity;
use App\Contracts\HasPermissions as HasPermissionsContract;
use App\Models\Entity;
use App\Models\Media\MediaEntity;
use App\Models\Person;
use App\Traits\Enumerable;
use App\Traits\Models\DoesSqlStuff;
use App\Traits\Models\HasAnEntity as HasAnEntityTrait;
use App\Traits\Models\HasPermissions;
use App\Traits\Presentable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class BlogPost extends Model implements HasPermissionsContract, EnumerableContract, HasAnEntity
{
    use Presentable, Enumerable, HasPermissions, DoesSqlStuff, HasAnEntityTrait;

    const PERMISSION_VIEW = 0b1;
    const PERMISSION_ADD = 0b10;
    const PERMISSION_EDIT = 0b100;
    const PERMISSION_DELETE = 0b1000;

//    public $timestamps = false;
    protected $primaryKey = 'blog_post_id';
    protected $fillable = [
        'person_id',
        'blog_status_id',
        'blog_post_title',
        'blog_post_slug',
        'blog_post_content',
        'blog_post_excerpt',
        'blog_post_is_sticky',
        'published_at'
    ];
    protected $hidden = [
        'person_id',
        'blog_status_id'
    ];
    protected $sortable = [
        'blog_post_title'
    ];
    public static $slugColumn = 'blog_post_slug';

    public static function boot()
    {
        parent::boot();

        static::creating(
            function ($model) {
                $model->blog_post_slug = str_slug(
                    substr($model->blog_post_title, 0, 95),
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

    /**
     * @link https://laravel.com/docs/5.7/eloquent#local-scopes
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return \Illuminate\Database\Eloquent\Builder $builder
     */
    public function scopeStatus(Builder $builder)
    {
        return $builder->join(
            'blog_status',
            'blog_status.blog_status_id',
            '=',
            'blog_posts.blog_status_id'
        );
    }

    /**
     * @link https://laravel.com/docs/5.7/eloquent#local-scopes
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return \Illuminate\Database\Eloquent\Builder $builder
     */
    public function scopePerson(Builder $builder)
    {
        return $this->joinReverse($builder, Person::class);
    }

    /**
     * @link https://laravel.com/docs/5.7/eloquent#local-scopes
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param int|null $blogPostId
     * @return \Illuminate\Database\Eloquent\Builder $builder
     */
    public function scopeEntityType(Builder $builder, $blogPostId = null)
    {
        return $builder->join('entity_types', function ($q) use ($blogPostId) {
            $q->on('entity_types.entity_type_target_id', '=', 'blog_posts.blog_post_id')
                ->where('entity_types.entity_id', '=', Entity::BLOG_POSTS);
            if (!is_null($blogPostId)) {
                $q->where('entity_types.entity_type_target_id',
                    '=', $blogPostId);
            }
        });
    }

    /**
     * @link https://laravel.com/docs/5.7/eloquent#query-scopes
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return \Illuminate\Database\Eloquent\Builder $builder
     */
    public function scopeLabelRecords(Builder $builder)
    {
        return $this->join($builder, BlogLabelRecord::class);
    }

    /**
     * @link https://laravel.com/docs/5.7/eloquent#local-scopes
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return \Illuminate\Database\Eloquent\Builder $builder
     */
    public function scopeLabelTypes(Builder $builder)
    {
        return $builder->join('blog_label_types',
            'blog_label_records.blog_label_type_id',
            '=',
            'blog_label_types.blog_label_type_id'
        );
    }

    /**
     * @link https://laravel.com/docs/5.7/eloquent#local-scopes
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return \Illuminate\Database\Eloquent\Builder $builder
     */
    public function scopeCategories(Builder $builder)
    {
        return $builder->join('blog_categories', 'blog_categories.blog_label_type_id', '=',
            'blog_label_types.blog_label_type_id');
    }

    /**
     * @link https://laravel.com/docs/5.7/eloquent#local-scopes
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return \Illuminate\Database\Eloquent\Builder $builder
     */
    public function scopeCategory(Builder $builder)
    {
        return $builder->labelRecords()->labelTypes()->categories();

    }

    /**
     * @link https://laravel.com/docs/5.7/eloquent#local-scopes
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return \Illuminate\Database\Eloquent\Builder $builder
     */
    public function scopeImages($builder)
    {
        return MediaEntity::scopeImage($builder);
    }

}