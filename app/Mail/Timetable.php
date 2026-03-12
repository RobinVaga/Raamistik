<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class Timetable extends Mailable
{
    use Queueable, SerializesModels;

    
    public Collection $timetableEvents;
    public Carbon $startDate;
    public Carbon $endDate;

    
    public function __construct(
        Collection $timetableEvents,
        Carbon $startDate,
        Carbon $endDate
    ) {
        $this->timetableEvents = $timetableEvents;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.timetable',
            with: [
                'timetableEvents' => $this->timetableEvents,
                'startDate' => $this->startDate,
                'endDate' => $this->endDate,
            ],
        );
    }
}









