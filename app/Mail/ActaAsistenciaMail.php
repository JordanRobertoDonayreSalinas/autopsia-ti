<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ActaAsistenciaMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public $acta
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $correlativo = str_pad($this->acta->id, 3, '0', STR_PAD_LEFT);
        return new Envelope(
            subject: "📄 Acta de Asistencia Técnica Firmada – AAT Nº {$correlativo}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.acta-asistencia',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        if ($this->acta->firmado_pdf) {
            $absolutePath = storage_path('app/public/' . $this->acta->firmado_pdf);
            
            if (file_exists($absolutePath)) {
                $nombreEstablecimiento = mb_strtoupper($this->acta->establecimiento->nombre ?? 'ESTABLECIMIENTO', 'UTF-8');
                $correlativo = str_pad($this->acta->id, 3, '0', STR_PAD_LEFT);
                
                return [
                    Attachment::fromPath($absolutePath)
                        ->as("AAT_Nro_{$correlativo}_{$nombreEstablecimiento}.pdf")
                        ->withMime('application/pdf'),
                ];
            }
        }

        return [];
    }
}
