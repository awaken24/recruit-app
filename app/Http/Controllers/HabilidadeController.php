<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Habilidade;
use App\Models\LogErrors;
use App\Models\LogSuccess;
use Illuminate\Support\Facades\Auth;

class HabilidadeController extends BaseController
{
    public function index(Request $request)
    {
        try {
            $habilidades = Habilidade::orderBy('nome')->get();

            LogSuccess::create([
                'route' => $request->url(),
                'success_message' => 'As habilidades foram obtidas com sucesso!',
                'user_id' => Auth::id() ?? null
            ]);

            return $this->success_data_response('Habilidades recuperadas com sucesso', $habilidades);
        } catch (\Exception $exception) {
            LogErrors::create([
                'route' => $request->url(),
                'error_message' => $exception->getMessage(),
                'user_id' => Auth::id() ?? null
            ]);
            return $this->error_response('Erro ao consultar as habilidades.', $exception->getMessage());
        }
    }
}
