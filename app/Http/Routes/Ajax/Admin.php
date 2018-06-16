<?php namespace App\Http\Routes\Ajax;

use Illuminate\Routing\Router;

class Admin
{
    public function bind(Router $router)
    {
        $router->group([
            'prefix' => '/ajax/admin',
            'namespace' => 'App\Http\Controllers\Ajax\Admin',
        ],
            function (Router $r) {
                $r->group([
                    'middleware' => ['auth.admin','admin']
                ], call_user_func('static::routes'));
            }
        );
    }

    public static function routes()
    {
        return function (Router $r) {
            $r->get('users', 'User@index')
                ->middleware('can:view,App\Models\User');
            $r->get('users/session', 'User@session');
            $r->get('users/{user}', 'User@edit')
                ->middleware('can:update,App\Models\User');
            $r->patch('users/{user}', 'User@update')
                ->middleware('can:delete,App\Models\User');
            $r->delete('users/{user}', 'User@destroy')
                ->middleware('can:delete,App\Models\User');
            $r->post('users/batch/delete', 'User@batchDestroy')
                ->middleware('can:delete,App\Models\User');
            $r->get('users/search/{search}', 'User@search')
                ->middleware('can:view,App\Models\User');

            $r->get('groups', 'Group@index')
                ->middleware('can:view,App\Models\Group');
            $r->get('groups/create', 'Group@add')
                ->middleware('can:add,App\Models\Group');
            $r->post('groups', 'Group@create')
                ->middleware('can:add,App\Models\Group');
            $r->get('groups/{group}', 'Group@edit')
                ->middleware('can:update,App\Models\Group');
            $r->patch('groups/{group}', 'Group@update')
                ->middleware('can:update,App\Models\Group');
            $r->delete('groups/{group}', 'Group@destroy')
                ->middleware('can:delete,App\Models\Group');

            $r->get('members/{group}/search/{search}', 'GroupMember@search')
                ->middleware('can:view,App\Models\Group');
            $r->get('members/{group}', 'GroupMember@index')
                ->middleware('can:view,App\Models\Group');
            $r->patch('members/{group}', 'GroupMember@update')
                ->middleware('can:view,App\Models\Group');

            $r->patch('settings/profile', 'Settings\Profile@update');
            $r->patch('settings/password', 'Settings\Password@update');

            $r->patch('settings/password', 'Settings\Password@update');
            $r->patch('settings/profile', 'Settings\Profile@update');
            $r->patch('settings/avatar', 'Settings\Profile@setAvatar');
            $r->delete('settings/avatar/{uuid}', 'Settings\Profile@deleteAvatar');
            $r->get('settings/avatar', 'Settings\Profile@avatar');

            $r->post('media/add', 'Media@add')
                ->middleware('can:update,App\Models\User');
            $r->post('media/crop', 'Media@crop')
                ->middleware('can:update,App\Models\User');

        };
    }
}