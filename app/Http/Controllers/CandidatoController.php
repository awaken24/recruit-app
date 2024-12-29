<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exceptions\CustomException;
use Illuminate\Support\Facades\{DB, Validator};
use App\Models\{
    Candidato,
    Endereco,
    Usuario
};

class CandidatoController extends BaseController
{

    public function salvarUsuario(Request $request)
    {
        try {
            DB::beginTransaction();

            // $request->validate([
            //     'email' => 'required|email|unique:usuarios,email',
            //     'password' => 'required|min:8|confirmed',
            // ]);

            $usuario = new Usuario();
            $usuario->email = $request->input('email');
            $usuario->password = Hash::make($request->input('password'));
            $usuario->usuarioable_type = Candidato::class;
            $usuario->save();

            DB::commit();
            return $this->success_response('Usuário salvo com sucesso.');
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->error_response('Erro ao salvar usuário.', $exception->getMessage());
        }
    }

    public function salvar(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nome_completo' => 'required|string|max:255',
                'email' => 'required|email|unique:usuarios,email',
                'telefone' => 'nullable|string|max:20',
                'data_nascimento' => 'nullable|date',
                'cpf' => 'nullable|string|max:14|unique:candidatos,cpf',
                'genero' => 'nullable|string|max:20',
                
                'endereco.cep' => 'required|string|max:9',
                'endereco.logradouro' => 'required|string|max:255',
                'endereco.numero' => 'required|string|max:20',
                'endereco.complemento' => 'nullable|string|max:255',
                'endereco.bairro' => 'required|string|max:255',
                'endereco.cidade' => 'required|string|max:255',
                'endereco.estado' => 'required|string|max:2',
            ]);

            if ($validator->fails()) {
                throw new CustomException("Erro de validação: {$validator->errors()}.", 422);
            }

            DB::beginTransaction();

            $candidato = new Candidato();
            $candidato->nome_completo = $request->input('nome_completo');
            $candidato->telefone = $request->input('telefone');
            $candidato->data_nascimento = $request->input('data_nascimento');
            $candidato->cpf = $request->input('cpf');
            $candidato->genero = $request->input('genero');
            $candidato->save();

            $enderecoData = $request->input('endereco');
            $endereco = new Endereco();
            $endereco->fill($enderecoData);
            $endereco->enderecavel_type = Candidato::class;
            $endereco->enderecavel_id = $candidato->id;
            $endereco->save();

            $candidato->usuario()->create([
                'email' => $request->email,
                'password' => bcrypt($request->password ?? str_random(10)),
                'nome' => $request->input('nome_completo'),
            ]);

            DB::commit();
            return $this->success_response('Candidato cadastrado.');

        } catch (CustomException $exception) {
            DB::rollBack();
            return $this->error_response($exception->getMessage(), null, $exception->getCode());
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->error_response('Erro ao cadastrar candidato.', $exception->getMessage());
        }
    }
}
