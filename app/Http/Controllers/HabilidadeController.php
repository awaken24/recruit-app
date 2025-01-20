<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Habilidade;

class HabilidadeController extends BaseController
{
    public function index()
    {
        try {
            $habilidades = Habilidade::orderBy('nome')->get();
            return $this->success_data_response('Habilidades recuperadas com sucesso', $habilidades);
        } catch (\Exception $e) {
            return $this->error_response('Erro ao consultar as habilidades.', $exception->getMessage());
        }
    }
}
