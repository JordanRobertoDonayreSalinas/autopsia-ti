<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ActaImplementacionMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public $acta,
        public $moduloNombre
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "📄 Acta de Implementación Firmada – {$this->moduloNombre} #{$this->acta->id}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.acta-implementacion',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        if ($this->acta->archivo_pdf) {
            // Usamos la ruta absoluta al archivo para mayor robustez,
            // especialmente en entornos donde el driver 'public' puede variar.
            $absolutePath = storage_path('app/public/' . $this->acta->archivo_pdf);
            
            if (file_exists($absolutePath)) {
                return [
                    Attachment::fromPath($absolutePath)
                        ->as("Acta_Firmada_{$this->acta->id}.pdf")
                        ->withMime('application/pdf'),
                ];
            }
        }

        return [];
    }
}
