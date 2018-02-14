<?php

namespace App\Traits\Models;

trait HasASystemEntity
{
    /**
     * Classes using this trait should define a systemEntityID
     * whose value matches a record in the system_entities table
     *
     * @see \App\Models\SystemEntity
     * @return int
     */
    public function getSystemEntity(){
        return $this->systemEntityID;
    }

}
