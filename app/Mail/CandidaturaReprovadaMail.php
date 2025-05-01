<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CandidaturaReprovadaMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $conteudo;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(string $conteudo)
    {
        $this->conteudo = $conteudo;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Atualização sobre sua candidatura')
            ->view('emails.candidatura-reprovada')
            ->with(['conteudo' => $this->conteudo]);
    }
}
