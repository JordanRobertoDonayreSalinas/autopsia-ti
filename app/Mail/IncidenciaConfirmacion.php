<?php

namespace App\Mail;

use App\Models\Incidencia;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class IncidenciaConfirmacion extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Incidencia $incidencia) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "✅ Ticket #{$this->incidencia->id} recibido – Mesa de Ayuda SIHCE",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.incidencia-confirmacion',
        );
    }
}
