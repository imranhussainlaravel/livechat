<x-mail::message>
    # Friendly Reminder

    You have a scheduled follow-up for a chat.

    **Visitor:** {{ $followup->chat->visitor->name }} ({{ $followup->chat->visitor->email }})
    **Subject:** {{ $followup->chat->subject ?? 'N/A' }}
    **Scheduled Time:** {{ \Carbon\Carbon::parse($followup->followup_time)->format('Y-m-d H:i') }}

    @if($followup->notes)
    **Notes:**
    {{ $followup->notes }}
    @endif

    Please review the chat and complete the follow-up as soon as possible.

    <x-mail::button :url="config('app.url') . '/agent/dashboard'">
        View Dashboard
    </x-mail::button>

    Thanks,<br>
    {{ config('app.name') }}
</x-mail::message>