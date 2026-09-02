<?php

declare(strict_types=1);

namespace App\Livewire;

use App\AIProfile;
use Livewire\Component;

class AiAvatarChat extends Component
{
    public array $profiles = [];

    public function mount(): void
    {
        $this->profiles = AIProfile::query()
            ->where('is_active', true)
            ->select(['id', 'name', 'static_image_path'])
            ->orderBy('name')
            ->get()
            ->toArray();
    }

    public function render()
    {
        return view('livewire.ai-avatar-chat');
    }
}
