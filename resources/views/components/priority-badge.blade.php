@php
    use App\Enums\TicketPriority;

    $priority = $priority ?? $ticket->priority;
    $enumPriority = $priority instanceof TicketPriority ? $priority : TicketPriority::tryFrom($priority);
@endphp

@if($enumPriority)
    <span class="badge {{ $enumPriority->badgeClass() }}">
        <i class="fa-solid fa-{{ $enumPriority->icon() }}"></i>
        {{ $enumPriority->label() }}
    </span>
@else
    <span class="badge badge-secondary">Unknown</span>
@endif
