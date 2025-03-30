<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Vaga;
use Carbon\Carbon;

class MarcarVagasVencidas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'marcar:vagas-vencidas';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $hoje = Carbon::today();

        $vagas = Vaga::where('status', '!=', 'vencida')->get();
    
        $contador = 0;
    
        foreach ($vagas as $vaga) {
            $vencida = false;
    
            if ($vaga->receber_candidaturas_ate) {
                $dataLimite = Carbon::parse($vaga->receber_candidaturas_ate);
                if ($dataLimite->lessThan($hoje)) {
                    $vencida = true;
                }
            } else {
                $dataCriacao = Carbon::parse($vaga->created_at);
                if ($dataCriacao->diffInDays($hoje) > 30) {
                    $vencida = true;
                }
            }
    
            if ($vencida) {
                $vaga->status = 'vencida';
                $vaga->save();
                $contador++;
            }
        }
    
        $this->info("Total de vagas marcadas como vencidas: $contador");
    }
}
