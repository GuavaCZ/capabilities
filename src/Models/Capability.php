<?php

namespace Guava\Capabilities\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Auth\User;

class Capability extends Model
{
    public $timestamps = false;

    public function getFillable(): array
    {
        return [
            'name',
            'title',
            'entity_type',
            'entity_id',
        ];
    }

    public function users(): MorphToMany
    {
        $pivot = [];

        if (config('capabilities.tenancy', false)) {
            $pivot[] = config('capabilities.tenant_column', 'tenant_id');
        }

        return $this
            ->morphedByMany(
                config('capabilities.user_class', User::class),
                'assignee',
                'assigned_capabilities',
                'assignee_id',
                'capability_id',
            )
            ->withPivot($pivot)
        ;
    }

    public function roles(): MorphToMany
    {
        $pivot = [];

        if (config('capabilities.tenancy', false)) {
            $pivot[] = config('capabilities.tenant_column', 'tenant_id');
        }

        return $this
            ->morphedByMany(
                config('capabilities.role_class', Role::class),
                'assignee',
                'assigned_roles',
                'assignee_id',
                'role_id',
            )
            ->withPivot($pivot)
        ;
    }

    protected function entity(): Attribute
    {
        return Attribute::make(
            get: function () {
                $model = Relation::getMorphedModel($this->entity_type) ?? $this->entity_type;

                if (! $model) {
                    return null;
                }

                return $model::find($this->entity_id);
            },
            set: function (null | Model | string $value) {
                $this->entity_type = $value instanceof Model ? $value->getMorphClass() : $value;
                $this->entity_id = $value instanceof Model ? $value->getKey() : null;
            }
        );
    }
}
