<?php

namespace FKS\Web\OutputModifiers;

class PrefixDateTime implements OutputModifier
{
    public function modify(string $output = ''): string
    {
        return '<span class="text-dimmed">'.now()->format('Y-m-d H:i:s').'</span><br>'.$output;
    }
}
