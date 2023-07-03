<?php

namespace mindtwo\NativeEnum\Tests;

use mindtwo\NativeEnum\Tests\Enums\WithDefaultEnum;
use mindtwo\NativeEnum\Tests\Models\TestModel;
use Orchestra\Testbench\TestCase;

class EnumLaravelIntegrationTest extends TestCase
{
    public function testValueIsCasted()
    {
        $model = TestModel::create([
            'enum' => WithDefaultEnum::EXAMPLE_1,
        ]);

        $this->assertEquals($model->enum, WithDefaultEnum::EXAMPLE_1);
    }

    public function testValueIsCastedToDefault()
    {
        $model = TestModel::create();

        dd($model->enum);

        $this->assertEquals($model->enum, WithDefaultEnum::EXAMPLE_1);
    }

    /**
     * Define database migrations.
     *
     * @return void
     */
    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/migrations');
    }
}
