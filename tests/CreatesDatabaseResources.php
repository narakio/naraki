<?php namespace Tests;

use Naraki\Sentry\Models\Group;
use Naraki\Sentry\Models\GroupMember;
use Naraki\Sentry\Models\Person;
use Naraki\Sentry\Models\User;

trait CreatesDatabaseResources
{
    protected function createGroup($attributes = [], $times = 1)
    {
        return factory(Group::class)->create($attributes);
    }

    /**
     * @param int $times
     * @return \Naraki\Sentry\Models\User|\Naraki\Sentry\Models\User[]
     */
    protected function createUser($times = 1)
    {
        $u = [];
        for ($i = 0; $i < $times; $i++) {
            $u[$i] = factory(User::class)->create([]);
            factory(Person::class)->create(['user_id' => $u[$i]->getKey()]);
        }

        return (count($u) === 1)
            ? User::query()->where('users.user_id', '=', $u[0]->getKey())->first()
            : $u;
    }

    /**
     * @param string $model
     * @return int
     */
    protected function cnt($model)
    {
        /**
         * @var \Illuminate\Database\Eloquent\Model $m
         */
        $m = new $model();
        return $m->newQuery()->count($m->getQualifiedKeyName());
    }

    /**
     * @param \Naraki\Sentry\Models\User|\Illuminate\Database\Eloquent\Model $user
     * @param \Naraki\Sentry\Models\Group|\Illuminate\Database\Eloquent\Model $group
     */
    protected function assignUserToGroup($user, $group)
    {
        if (is_array($user)) {
            foreach ($user as $item) {
                $members[] = ['user_id' => $item->user_id, 'group_id' => $group->group_id];
            }
        } else {
            $members = ['user_id' => $user->user_id, 'group_id' => $group->group_id];
        }
        GroupMember::insert($members);
    }

}