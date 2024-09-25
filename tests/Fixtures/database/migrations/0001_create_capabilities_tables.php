<?php

use Guava\Capabilities\Models\Capability;
use Guava\Capabilities\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('capabilities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('title')->nullable();
            // TODO: Add "related" column so that a permission can be related to a specific record, like course.view.1 to view course of id 1
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('title')->nullable();

            if (config('capabilities.tenancy', false)) {
                $table->foreignIdFor(config('capabilities.tenant_class'))
                    ->nullable()
                    ->constrained()
                    ->cascadeOnDelete()
                ;
            }
        });

        Schema::create('assigned_capabilities', function (Blueprint $table) {
            $table->foreignIdFor(Capability::class)
                ->constrained()
                ->cascadeOnDelete()
            ;
            $table->morphs('assignee');

            if (config('capabilities.tenancy', false)) {
                $table->foreignIdFor(config('capabilities.tenant_class'))
                    ->nullable()
                    ->constrained()
                    ->cascadeOnDelete()
                ;
            }
        });

        Schema::create('assigned_roles', function (Blueprint $table) {
            $table->foreignIdFor(Role::class)
                ->constrained()
                ->cascadeOnDelete()
            ;
            $table->morphs('assignee');

            if (config('capabilities.tenancy', false)) {
                $table->foreignIdFor(config('capabilities.tenant_class'))
                    ->nullable()
                    ->constrained()
                    ->cascadeOnDelete()
                ;
            }
        });
    }
};
