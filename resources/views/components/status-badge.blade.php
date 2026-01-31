@php
    use App\Enums\TicketStatus;

    $status = $status ?? $ticket->status;
    $enumStatus = $status instanceof TicketStatus ? $status : TicketStatus::tryFrom($status);
@endphp

@if($enumStatus)
    <span class="badge {{ $enumStatus->badgeClass() }}">
        {{ $enumStatus->label() }}
    </span>
@else
    <span class="badge badge-secondary">Unknown</span>
@endif
