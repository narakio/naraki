<?php namespace Naraki\Core\Composers;

use Naraki\Core\Facades\JavaScript;
use Naraki\Core\Composer;
use Naraki\Sentry\Models\User;
use Naraki\Permission\Facades\Permission;

class Admin extends Composer
{

    /**
     * @param \Illuminate\View\View $view
     */
    public function compose($view)
    {
        $tmp = auth()->user();
        $user = null;
        if ($tmp instanceof User) {
            $user = $tmp->only(['username', 'system_events_subscribed']);
        }

        JavaScript::putArray([
            'appName' => config('app.name'),
            'locale' => app()->getLocale(),
            'user' => $user,
            'permissions' => auth('jwt')->check()
                ? Permission::cacheUserPermissions(auth('jwt')->user()->getAttribute('entity_type_id'))
                : null
        ]);

        JavaScript::bindJsVariablesToView();
    }


}