<?php

namespace Tests\App\Yoyo;

use Clickfwd\Yoyo\Component;

class ProtectedMethods extends Component
{
    protected function secret()
    {
        // Cannot be accessed through direct request
    }
}
