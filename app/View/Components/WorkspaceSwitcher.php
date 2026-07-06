<?php

namespace App\View\Components;

use App\Models\User;
use App\Services\UserWorkspaceService;
use Illuminate\Support\Collection;
use Illuminate\View\Component;
use Illuminate\View\View;

class WorkspaceSwitcher extends Component
{
    public Collection $contexts;

    public ?string $activeKey;

    /**
     * @param  iterable<array>|null  $contexts
     */
    public function __construct(?iterable $contexts = null, ?string $activeKey = null)
    {
        /** @var User|null $user */
        $user = auth()->user();
        $workspaceService = app(UserWorkspaceService::class);

        $this->contexts = collect($contexts ?? ($user ? $workspaceService->contextsFor($user) : []))
            ->values();

        $this->activeKey = $activeKey ?? ($user ? $workspaceService->activeKey($user) : null);
    }

    public function shouldRender(): bool
    {
        return $this->contexts->count() > 1;
    }

    public function render(): View
    {
        return view('components.workspace-switcher');
    }
}
