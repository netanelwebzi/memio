<?php

/*
 * This file is part of the Medio project.
 *
 * (c) Loïc Chardonnet <loic.chardonnet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\Gnugat\Medio\Factory;

use PhpSpec\ObjectBehavior;

class TypeArgumentFactorySpec extends ObjectBehavior
{
    function it_names_objects_after_their_type()
    {
        $argument = $this->make('Gnugat\Medio\Factory\ArgumentFactory');

        $argument->getName()->shouldBe('argumentFactory');
        $argument->isObject()->shouldBe(true);
    }

    function it_gives_generic_name_to_callables()
    {
        $argument = $this->make('callable');

        $argument->getName()->shouldBe('argument');
        $argument->isObject()->shouldBe(false);
    }

    function it_gives_generic_name_to_non_objects()
    {
        $argument = $this->make('string');

        $argument->getName()->shouldBe('argument');
        $argument->isObject()->shouldBe(false);
    }
}
