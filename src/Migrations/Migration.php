<?php

namespace Onlyoung4u\BunnyMigration\Migrations;

use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Builder;

abstract class Migration extends \Illuminate\Database\Migrations\Migration
{
    protected Connection $db;

    public function setDB(Connection $db): void
    {
        $this->db = $db;
    }

    protected function schema(): Builder
    {
        return $this->db->getSchemaBuilder();
    }

    abstract public function up(): void;

    abstract public function down(): void;
}