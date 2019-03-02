<?php namespace App\Http\Controllers\Ajax\Admin;

use App\Contracts\Models\Blog as BlogProvider;
use App\Contracts\Models\Media as MediaProvider;
use App\Contracts\Models\User as UserProvider;
use App\Filters\Blog as BlogFilter;
use App\Http\Controllers\Admin\Controller;
use App\Http\Requests\Admin\CreateBlogPost;
use App\Http\Requests\Admin\UpdateBlogPost;
use App\Models\Blog\BlogStatus;
use App\Models\Entity;
use App\Models\Media\MediaImgFormat;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class Blog extends Controller
{
    /**
     * @param \App\Filters\Blog $filter
     * @param \App\Contracts\Models\Blog|\App\Support\Providers\Blog $blogRepo
     * @return array
     */
    public function index(BlogFilter $filter, BlogProvider $blogRepo)
    {
        return [
            'table' => $blogRepo->buildList([
                \DB::raw('null as selected'),
                'blog_post_title',
                'full_name',
                'blog_post_slug'
            ])->filter($filter)->paginate(25),
            'columns' => $blogRepo->createModel()->getColumnInfo([
                'blog_post_title' => (object)[
                    'name' => trans('js-backend.db.blog_post_title'),
                    'width' => '50%'
                ],
                'full_name' => (object)[
                    'name' => trans('js-backend.db.full_name'),
                    'width' => '30%'
                ]
            ], $filter)
        ];
    }

    /**
     * @return array
     */
    public function add()
    {
        return [
            'record' => [
                'blog_status' => BlogStatus::getConstantByID(BlogStatus::BLOG_STATUS_DRAFT),
                'blog_post_person' => $this->user->getAttribute('full_name'),
                'person_slug' => $this->user->getAttribute('person_slug'),
                'categories' => [],
                'tags' => [],
            ],
            'status_list' => BlogStatus::getConstants('BLOG'),
            'blog_categories' => \App\Support\Trees\BlogCategory::getTree(),
            'thumbnails' => []
        ];
    }

    /**
     * @param $slug
     * @param \App\Contracts\Models\Blog|\App\Support\Providers\Blog $blogRepo
     * @param \App\Contracts\Models\Media|\App\Support\Providers\Media $mediaRepo
     * @return array
     */
    public function edit($slug, BlogProvider $blogRepo, MediaProvider $mediaRepo)
    {
        $record = $blogRepo->buildOneBySlug(
            $slug,
            [
                'blog_posts.blog_post_id',
                'blog_post_title',
                'blog_post_slug',
                'blog_post_content',
                'blog_post_excerpt',
                'published_at',
                'blog_posts.blog_status_id',
                'blog_status_name as blog_status',
                'people.full_name as blog_post_person',
                'entity_type_id'
            ])->first();
        if (is_null($record)) {
            return response(trans('error.http.500.blog_post_not_found'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        $blogPost = $record->toArray();
        $categories = \App\Support\Trees\BlogCategory::getTreeWithSelected($blogPost['blog_post_id']);
        $blogPost['categories'] = $categories->categories;
        $blogPost['tags'] = $blogRepo->tag()->getByPost($blogPost['blog_post_id']);
        unset($blogPost['entity_type_id'], $blogPost['blog_post_id']);
        return [
            'record' => $blogPost,
            'status_list' => BlogStatus::getConstants('BLOG'),
            'blog_categories' => $categories->tree,
            'url' => $this->getPostUrl($record),
            'source_types'=>$blogRepo->source()->listTypes(),
            'sources'=>$blogRepo->source()
                ->buildByBlogSlug(
                    $record->getAttribute('blog_post_slug')
                )->get()->toArray(),
            'blog_post_slug' => $record->getAttribute('blog_post_slug'),
            'thumbnails' => $mediaRepo->image()->getImages(
                $record->getAttribute('entity_type_id'))
        ];
    }

    /**
     * @param \App\Http\Requests\Admin\CreateBlogPost $request
     * @param \App\Contracts\Models\Blog|\App\Support\Providers\Blog $blogRepo
     * @param \App\Contracts\Models\User|\App\Support\Providers\User $userRepo
     * @return array
     */
    public function create(CreateBlogPost $request, BlogProvider $blogRepo, UserProvider $userRepo)
    {
        try {
            $post = $blogRepo->createOne(
                $request->all(),
                $userRepo->person()->buildOneBySlug(
                    $request->getPersonSlug(),
                    [$userRepo->person()->getKeyName()]
                )
            );

            $blogRepo->category()->attachToPost($request->getCategories(), $post);
            $blogRepo->tag()->attachToPost($request->getTags(), $post);
        } catch (\Exception $e) {
            return response($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return (
        [
            'url' => $this->getPostUrl($post),
            'blog_post_slug' => $post->getAttribute('blog_post_slug'),
        ]);
    }

    /**
     * @param $slug
     * @param \App\Http\Requests\Admin\UpdateBlogPost $request
     * @param \App\Contracts\Models\Blog|\App\Support\Providers\Blog $blogRepo
     * @param \App\Contracts\Models\User|\App\Support\Providers\User $userRepo
     * @return array
     */
    public function update($slug, UpdateBlogPost $request, BlogProvider $blogRepo, UserProvider $userRepo)
    {
        $person = $request->getPersonSlug();

        if (!is_null($person)) {
            $query = $userRepo->person()->buildOneBySlug(
                $person,
                [$userRepo->person()->getKeyName()]
            )->first();
            if (!is_null($query)) {
                $request->setPersonSlug($query[$userRepo->person()->getKeyName()]);
            }
        }
        $post = $blogRepo->updateOne($slug, $request->all());
        $blogRepo->category()->updatePost($request->getCategories(), $post);
        $blogRepo->tag()->updatePost($request->getTags(), $post);
        return (
        [
            'url' => $this->getPostUrl($post),
            'blog_post_slug' => $post->getAttribute('blog_post_slug'),
        ]);
    }

    /**
     * @param $slug
     * @param \App\Contracts\Models\Blog|\App\Support\Providers\Blog $blogRepo
     * @param \App\Contracts\Models\Media|\App\Support\Providers\Media $mediaRepo
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Exception
     * @throws \Throwable
     */
    public function destroy($slug, BlogProvider $blogRepo, MediaProvider $mediaRepo)
    {
        try {
            $mediaUuids = $mediaRepo->image()
                ->getImagesFromSlug(
                    $slug,
                    Entity::BLOG_POSTS,
                    ['media_uuid']
                )->pluck('media_uuid')->all();
            $deleteResult = \DB::transaction(function () use ($slug, $blogRepo, $mediaRepo, $mediaUuids) {
                $mediaRepo->image()->delete($mediaUuids, Entity::BLOG_POSTS);
                return $blogRepo->deleteBySlug($slug);
            });
        } catch (\Exception $e) {
            return response(trans('error.http.500.general_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response($deleteResult, Response::HTTP_OK);
    }

    /**
     * @param \App\Contracts\Models\Blog|\App\Support\Providers\Blog $blogRepo
     * @param \App\Contracts\Models\Media|\App\Support\Providers\Media $mediaRepo
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Throwable
     */
    public function batchDestroy(BlogProvider $blogRepo, MediaProvider $mediaRepo, Request $request)
    {
        $input = $request->only('posts');
        if (isset($input['posts'])) {
            $postSlugs = $input['posts'];
            $mediaUuids = [];
            foreach ($postSlugs as $slug) {
                $uuids = $mediaRepo->image()->getImagesFromSlug($slug, Entity::BLOG_POSTS, ['media_uuid'])
                    ->pluck('media_uuid')->all();
                if (!empty($uuids) && !is_null($uuids)) {
                    $mediaUuids = array_merge($mediaUuids, $uuids);
                }
            }
            \DB::transaction(function () use ($postSlugs, $mediaUuids, $blogRepo, $mediaRepo) {
                $mediaRepo->image()->delete($mediaUuids, Entity::BLOG_POSTS);
                $blogRepo->deleteBySlug($postSlugs);
            });
            return response(null, Response::HTTP_NO_CONTENT);
        }
        return response(trans('error.http.500.general_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * @param string $slug
     * @param string $uuid
     * @param \App\Contracts\Models\Media|\App\Support\Providers\Media $mediaRepo
     * @return mixed
     */
    public function setFeaturedImage($slug, $uuid, MediaProvider $mediaRepo)
    {
        $mediaRepo->image()->setAsUsed($uuid);
        $media = $mediaRepo->image()->getOne($uuid, ['media_extension']);
        if (!is_null($media)) {
            $mediaRepo->image()->cropImageToFormat(
                $uuid,
                Entity::BLOG_POSTS,
                \App\Models\Media\Media::IMAGE,
                $media->getAttribute('media_extension'),
                MediaImgFormat::FEATURED
            );
        }
        return $mediaRepo->image()->getImagesFromSlug($slug, Entity::BLOG_POSTS)->toArray();
    }

    /**
     * @param string $slug
     * @param string $uuid
     * @param \App\Contracts\Models\Media|\App\Support\Providers\Media $mediaRepo
     * @return mixed
     * @throws \Exception
     */
    public function deleteImage($slug, $uuid, MediaProvider $mediaRepo)
    {
        $mediaRepo->image()->delete(
            $uuid,
            Entity::BLOG_POSTS);
        return $mediaRepo->image()->getImagesFromSlug($slug, Entity::BLOG_POSTS)->toArray();
    }

    /**
     * @param \App\Models\Blog\BlogPost $post
     * @return string
     */
    private function getPostUrl($post)
    {
        $params = [
            'slug' => $post->getAttribute('blog_post_slug'),
        ];
        if ($post->getAttribute('blog_status_id') != BlogStatus::BLOG_STATUS_PUBLISHED) {
            $params['preview'] = true;
        }
        return route_i18n('blog', $params);
    }

}