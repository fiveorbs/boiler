<?php

declare(strict_types=1);

namespace FiveOrbs\Boiler;

enum SectionMode
{
	case Assign;
	case Append;
	case Prepend;
	case Closed;
}
