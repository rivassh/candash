<?php

namespace App\Services\Search;

use App\Models\Skill;

class SkillMatcherCache
{
    private array $index = [];

    public function __construct()
    {
        $this->load();
    }

    public function load(): void
    {
        if (!empty($this->index)) return;
        Skill::where('is_active', true)->get(['id', 'name', 'normalized_name', 'aliases'])
            ->each(function (Skill $skill) {
                $this->index[$skill->normalized_name] = $skill->id;
                foreach ($skill->aliases ?? [] as $alias) {
                    $this->index[mb_strtolower(trim($alias))] = $skill->id;
                }
            });
    }

    public function resolveId(string $name): ?int
    {
        return $this->index[mb_strtolower(trim($name))] ?? null;
    }
}