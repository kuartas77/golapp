<?php

namespace App\Relations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HasManyBindingRelation extends HasMany
{

    public function __construct($related, Model $parent,  $foreignKey = null, $localKey = null)
    {
        parent::__construct($related->newQuery(), $parent, $foreignKey, $localKey);
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
        // $whereIn = $this->whereInMethod($this->parent, $this->localKey);
        // $whereIn = $this->whereInMethod($this->parent, $this->foreignKey);
        $whereIn = 'whereIn';

        $this->getRelationQuery()->{$whereIn}(
            $this->foreignKey,
            $this->getKeys($models, $this->localKey)
        );
    }
}
