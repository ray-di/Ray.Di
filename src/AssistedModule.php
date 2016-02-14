<?php
/**
 * This file is part of the Ray.Di package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Di;

class AssistedModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind('Doctrine\Common\Annotations\Reader')->to('Doctrine\Common\Annotations\AnnotationReader');
        $this->bindInterceptor(
            $this->matcher->any(),
            $this->matcher->annotatedWith('Ray\Di\Di\Assisted'),
            ['\Ray\Di\AssistedInterceptor']
        );
    }
}
