<x-mail::message>
    # Hello {{ $ticket->chat->visitor->name }},

    Thank you for your interest! As discussed during your chat, please find your customized quotation below.

    **Details:**
    - **Product/Service:** {{ $ticket->chat->subject ?? 'Custom Service' }}
    - **Quoted Amount:** ${{ number_format($ticket->amount, 2) }}
    - **Assigned Agent:** {{ $ticket->agent->name }}

    @if($ticket->notes)
    **Additional Notes:**
    {{ $ticket->notes }}
    @endif

    If you have any questions or would like to proceed, please feel free to reply directly to this email or start a new chat on our website.

    <x-mail::button :url="config('app.url')">
        Visit Website
    </x-mail::button>

    Thanks,<br>
    {{ config('app.name') }}
</x-mail::message>