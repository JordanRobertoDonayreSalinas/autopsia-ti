<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ActaMonitoreoMail extends Mailable
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
        $numero = str_pad($this->acta->numero_acta ?? $this->acta->id, 5, '0', STR_PAD_LEFT);
        return new Envelope(
            subject: "📊 Acta de Monitoreo Firmada – N° {$numero}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.acta-monitoreo',
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
                $numero = str_pad($this->acta->numero_acta ?? $this->acta->id, 5, '0', STR_PAD_LEFT);
                $nombreEst = mb_strtoupper($this->acta->establecimiento->nombre ?? 'ESTABLECIMIENTO', 'UTF-8');

                return [
                    Attachment::fromPath($absolutePath)
                        ->as("Acta_Monitoreo_Nro_{$numero}_{$nombreEst}.pdf")
                        ->withMime('application/pdf'),
                ];
            }
        }

        return [];
    }
}
