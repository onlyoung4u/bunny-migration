<?php

namespace Onlyoung4u\BunnyMigration\Migrations;

class Migrator extends \Illuminate\Database\Migrations\Migrator
{
    public function resolve($file): object
    {
        $migration = parent::resolve($file);
        $migration->setDB($this->resolveConnection($migration->getConnection()));
        return $migration;
    }

    protected function resolvePath(string $path): object
    {
        $migration = parent::resolvePath($path);
        $migration->setDB($this->resolveConnection($migration->getConnection()));
        return $migration;
    }
}