<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

use function Laravel\Prompts\clear;
use function Laravel\Prompts\select;
use function Laravel\Prompts\table;

class Tabla extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:tabla {table?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Busca las entradas en una tabla';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $table = $this->argument('table');

        if (!$table) {
            $tablas = Schema::getTables(Schema::getCurrentSchemaName());
            $table = select(
                label: 'Selecciona una tabla',
                options: collect($tablas)->map(function (array $table) {
                    return $table['name'];
                })->all(),
                required: true,
            );
        }

        if (!Schema::hasTable($table)) {
            $this->error("La tabla '$table' no existe.");
            return 1;
        }

        $entries = DB::table($table)->get();

        if ($entries->isEmpty()) {
            $this->info("No hay entradas en la tabla '$table'.");
            return 0;
        }

        clear();
        $this->info("Entradas en la tabla '$table':");
        $this->info('Total: ' . $entries->count());
        table(
            headers: Schema::getColumnListing($table),
            rows: $entries->map(function ($entry) {
                return (array) $entry;
            }),
        );
    }
}
