<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
    @switch($icon)
        @case('home')
            <path d="m3 10 9-7 9 7M5 9v12h14V9M9 21v-7h6v7" />
            @break
        @case('journal')
            <path d="M5 4.5A2.5 2.5 0 0 1 7.5 2H20v18H7.5A2.5 2.5 0 0 0 5 22zM5 4.5V22M9 7h7M9 11h7" />
            @break
        @case('approval')
            <circle cx="12" cy="12" r="9" /><path d="m8 12 2.5 2.5L16 9" />
            @break
        @case('people')
        @case('attendance')
            <circle cx="9" cy="8" r="3" /><path d="M3 20v-1a6 6 0 0 1 12 0v1zM16 5.5a3 3 0 0 1 0 5.8M18 14a5 5 0 0 1 3 4.5V20h-4" />
            @break
        @case('class')
            <path d="M3 21V5l9-3 9 3v16M3 21h18M7 8h2M15 8h2M7 12h2M15 12h2M10 21v-5h4v5" />
            @break
        @case('calendar')
            <rect x="3" y="5" width="18" height="16" rx="2" /><path d="M16 3v4M8 3v4M3 10h18M8 15h3" />
            @break
        @case('report')
            <path d="M4 20V5M4 20h17M8 16v-5M13 16V7M18 16v-3" />
            @break
        @case('clock')
        @case('history')
            <circle cx="12" cy="12" r="9" /><path d="M12 7v5l3 2" />
            @break
        @case('person')
            <circle cx="12" cy="8" r="4" /><path d="M4 21a8 8 0 0 1 16 0" />
            @break
        @case('activity')
            <path d="M3 12h4l3-8 4 16 3-8h4" />
            @break
        @case('chat')
            <path d="M21 11.5a8.4 8.4 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.4 8.4 0 0 1-3.8-.9L3 21l1.9-5.7a8.4 8.4 0 0 1-.9-3.8A8.5 8.5 0 0 1 8.7 3.9a8.4 8.4 0 0 1 3.8-.9h.5a8.5 8.5 0 0 1 8 8z" />
            @break
        @case('logout')
            <path d="M10 17l5-5-5-5M15 12H3M12 3h6a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-6" />
            @break
        @default
            <circle cx="5" cy="12" r="1" /><circle cx="12" cy="12" r="1" /><circle cx="19" cy="12" r="1" />
    @endswitch
</svg>