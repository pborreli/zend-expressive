<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @see       https://github.com/zendframework/zend-expressive for the canonical source repository
 * @copyright Copyright (c) 2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Expressive\Container;

use Interop\Container\ContainerInterface;
use Whoops\Handler\JsonResponseHandler;
use Whoops\Run as Whoops;

/**
 * Create and return an instance of the Whoops runner.
 *
 * Register this factory as the service `Zend\Expressive\Whoops` in the
 * container of your choice. This service depends on two others:
 *
 * - 'config' (which should return an array or array-like object with a "whoops"
 *   key, containing the configuration for whoops).
 * - 'Zend\Expressive\WhoopsPageHandler', which should return a
 *   Whoops\Handler\PrettyPageHandler instance to register on the whoops
 *   instance.
 *
 * The whoops configuration can contain:
 *
 * <code>
 * 'whoops' => [
 *     'json_exceptions' => [
 *         'display'    => true,
 *         'show_trace' => true,
 *         'ajax_only'  => true,
 *     ]
 * ]
 * </code>
 *
 * All values are booleans; ommision of any implies boolean false.
 */
class WhoopsFactory
{
    /**
     * Create and return an instance of the Whoops runner.
     *
     * @param ContainerInterface $container
     * @return Whoops
     */
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->has('config') ? $container->get('config') : [];
        $config = isset($config['whoops']) ? $config['whoops'] : [];

        $whoops = new Whoops();
        $whoops->writeToOutput(false);
        $whoops->allowQuit(false);
        $whoops->pushHandler($container->get('Zend\Expressive\WhoopsPageHandler'));
        $this->registerJsonHandler($whoops, $config);
        $whoops->register();
        return $whoops;
    }

    /**
     * If configuration indicates a JsonResponseHandler, configure and register it.
     *
     * @param Whoops $whoops
     * @param array|\ArrayAccess $config
     */
    private function registerJsonHandler(Whoops $whoops, $config)
    {
        if (! isset($config['json_exceptions']['display'])
            || empty($config['json_exceptions']['display'])
        ) {
            return;
        }

        $handler = new JsonResponseHandler();

        if (isset($config['json_exceptions']['show_trace'])) {
            $handler->addTraceToOutput(true);
        }

        if (isset($config['json_exceptions']['ajax_only'])) {
            $handler->onlyForAjaxRequests(true);
        }

        $whoops->pushHandler($handler);
    }
}
