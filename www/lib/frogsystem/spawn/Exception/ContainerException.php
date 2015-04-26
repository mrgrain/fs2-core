<?php
namespace Frogsystem\Spawn\Exception;

use Interop\Container\Exception\ContainerException as ContainerExceptionInterface;

/**
 * Base interface representing a generic exception in a container.
 */
class ContainerException extends \Exception implements ContainerExceptionInterface
{
}
