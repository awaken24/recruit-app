<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Vaga;
use App\Models\Oportunidades;
use App\Models\Candidato;


class GerarOportunidades extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'oportunidades:gerar';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gera oportunidades para candidatos com compatibilidade >= 50%';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Iniciando geração de oportunidades...');

        $vagasAtivas = Vaga::where('status', 'ativa')->get();
        $candidatos = Candidato::all();

        foreach ($vagasAtivas as $vaga) {
            foreach ($candidatos as $candidato) {
                $compatibilidade = $vaga->calcularCompatibilidade($candidato);

                if ($compatibilidade >= 50) {
                    $jaExiste = Oportunidades::where('vaga_id', $vaga->id)
                        ->where('candidato_id', $candidato->id)
                        ->exists();

                    if (!$jaExiste) {
                        $oportunidade = new Oportunidades();
                        $oportunidade->vaga_id = $vaga->id;
                        $oportunidade->candidato_id = $candidato->id;
                        $oportunidade->compatibilidade = $compatibilidade;
                        $oportunidade->save();

                        $this->info("Oportunidade criada: Vaga {$vaga->id} - Candidato {$candidato->id} - {$compatibilidade}%");
                    }
                }
            }
        }

        $this->info('Processo concluído.');
    }
}
