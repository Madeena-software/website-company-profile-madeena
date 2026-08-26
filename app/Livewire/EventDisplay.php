<?php

namespace App\Livewire;

use App\Models\Event;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.display')]
class EventDisplay extends Component
{
    public Event $event;

    public function mount(Event $event)
    {
        $this->event = $event;
    }

    public function render()
    {
        $messages = $this->event->guestMessages()->visible()->latest()->get();

        return view('livewire.event-display', [
            'messages' => $messages,
        ])->title($this->event->name.' — Live Display | PT Madeena Karya Indonesia');
    }
}
