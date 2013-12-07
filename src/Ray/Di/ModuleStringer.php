<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

/**
 * String for module
 */
class ModuleStringer
{
    /**
     * Return module information as string
     *
     * @param AbstractModule $module
     *
     * @return string
     */
    public function toString(AbstractModule $module)
    {
        $output = '';
        foreach ((array)$module->bindings as $bind => $bindTo) {
            foreach ($bindTo as $annotate => $to) {
                $type = $to['to'][0];
                $output .= ($annotate !== '*') ? "bind:{$bind} annotatedWith:{$annotate}" : "bind:{$bind}";
                if ($type === 'class') {
                    $output .= " to:" . $to['to'][1];
                }
                if ($type === 'instance') {
                    $output .= $this->getInstanceString($to);
                }
                if ($type === 'provider') {
                    $provider = $to['to'][1];
                    $output .= " toProvider:" . $provider;
                }
                $output .= PHP_EOL;
            }
        }

        return $output;
    }

    /**
     * @param array $to
     *
     * @return string
     */
    private function getInstanceString(array $to)
    {
        $instance = $to['to'][1];
        $type = gettype($instance);
        switch ($type) {
            case "object":
                $instance = '(object) ' . get_class($instance);
                break;
            case "array":
                $instance = json_encode($instance);
                break;
            case "string":
                $instance = "'{$instance}'";
                break;
            case "boolean":
                $instance = '(bool) ' . ($instance ? 'true' : 'false');
                break;
            default:
                $instance = "($type) $instance";
        }
        return " toInstance:" . $instance;
    }
}
