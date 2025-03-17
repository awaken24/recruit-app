<?php

namespace App\Exceptions;

use Exception;

class CandidaturaException extends Exception{

    public static function usuarioNaoAutenticado(){
        return new self("Usuário não autenticado.", 401);
    }

    public static function apenasCandidato(){
        return new self("Apenas candidatos podem se candidatar a vagas.", 403);
    }

    public static function vagaNaoEncontrada(){
        return new self("A vaga não foi encontrada ou está inativa.", 404);
    }

    public static function candidaturaJaRealizada(){
        return new self("Você já se candidatou para essa vaga.", 409);
    }
}
