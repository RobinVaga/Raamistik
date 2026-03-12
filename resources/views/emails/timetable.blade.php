@component('mail::message')
# Minu tunniplaan


**Periood:** {{ $startDate->format('d.m.Y') }} – {{ $endDate->format('d.m.Y') }}

@foreach ($timetableEvents as $day => $events)
## {{ ucfirst($day) }}

@foreach ($events as $event)
@php
    $time = "{$event['timeStart']} - {$event['timeEnd']}";
    $name = $event['nameEt'] ?? 'Puudub nimi';
    $room = $event['rooms'][0]['roomCode'] ?? 'Tundmatu klass';
    $teachers = collect($event['teachers'] ?? [])
        ->pluck('name')
        ->implode(', ') ?: 'Puudub õpetaja';
@endphp

- **Kellaaeg:** {{ $time }}  
  **Aine:** {{ $name }}  
  **Klass:** {{ $room }}  
  **Õpetaja:** {{ $teachers }}

@endforeach

@endforeach

@endcomponent