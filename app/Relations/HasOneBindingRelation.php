<?php

namespace App\Relations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Contracts\Database\Eloquent\Builder;

class HasOneBindingRelation extends HasOne
{

    public function __construct(Builder $query, Model $parent, $foreignKey, $localKey)
    {
        parent::__construct($query, $parent, $foreignKey, $localKey);
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
