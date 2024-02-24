<?php

namespace App\Relations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BelongsToBindingRelation extends BelongsTo
{

    public function __construct($related, Model $owner, $foreignKey = null, $ownerKey = null, $relation = null)
    {
        parent::__construct($related->newQuery(), $owner, $foreignKey, $ownerKey, $relation);
    }

    /**
     * Set the constraints for an eager load of the relation.
     *
     * @param array $models
     *
     * @return void
     */
    public function addEagerConstraints(array $models)
    {
        $key = $this->related->getTable() . '.' . $this->ownerKey;

        // $whereIn = $this->whereInMethod($this->related, $this->foreignKey);
        $whereIn = 'whereIn';

        $this->query->{$whereIn}($key, $this->getEagerModelKeys($models));
    }
}
