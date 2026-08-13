<?php

namespace App\Mail;

use App\Models\Incidencia;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class IncidenciaNotificacionAdmin extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Incidencia $incidencia) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "🚨 Nueva incidencia #{$this->incidencia->id} pendiente – {$this->incidencia->nombre_establecimiento}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.incidencia-admin',
        );
    }
}
