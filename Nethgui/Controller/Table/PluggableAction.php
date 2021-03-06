<?php
namespace Nethgui\Controller\Table;

/*
 * Copyright (C) 2012 Nethesis S.r.l.
 *
 * This script is part of NethServer.
 *
 * NethServer is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * NethServer is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with NethServer.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Decorate an AbstractAction, allowing other (plugin) actions to extend it.
 * 
 * Collaborates with 
 * - PluginCollector, that actually holds the plugin actions
 * - AbstractAction, the decorated module
 * - TableController,  the parent module 
 *
 * @api
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 * @see Decorator pattern
 */
class PluggableAction extends \Nethgui\Controller\Table\AbstractAction implements \Nethgui\Module\ModuleCompositeInterface, \Nethgui\Component\DependencyInjectorAggregate
{
    /**
     * @var \Nethgui\Controller\Table\ActionPluginLoader
     */
    private $plugins;

    /**
     *
     * @var \Nethgui\Controller\Table\AbstractAction 
     */
    private $innerAction;

    /**
     *
     * @var string
     */
    private $pluginsPath;

    /**
     *
     * @var callable
     */
    private $dependencyInjector;

    /**
     * 
     * @param \Nethgui\Controller\Table\AbstractAction $action
     * @param string $pluginsPath 
     */
    public function __construct(\Nethgui\Controller\Table\AbstractAction $action, $pluginsPath = NULL)
    {
        if (is_null($pluginsPath)) {
            $id = \Nethgui\array_end(explode('\\', get_class($action)));
        } else {
            $id = \Nethgui\array_end(explode('/', $pluginsPath));
        }

        parent::__construct(); // empty identifier
        $this->innerAction = $action;
        $this->plugins = new \Nethgui\Controller\Table\PluginCollector($id);
        $this->plugins->setParent($action);
        $this->pluginsPath = $pluginsPath;
    }

    public function hasAdapter()
    {
        return $this->innerAction->hasAdapter();
    }

    public function getAdapter()
    {
        return $this->innerAction->getAdapter();
    }

    public function setAdapter(\Nethgui\Adapter\AdapterInterface $adapter)
    {
        $this->innerAction->setAdapter($adapter);        
        return $this;
    }

    public function getIdentifier()
    {
        return $this->innerAction->getIdentifier();
    }

    public function getAttributesProvider()
    {
        return $this->innerAction->getAttributesProvider();
    }

    protected function initializeAttributes(\Nethgui\Module\ModuleAttributesInterface $base)
    {
        return \Nethgui\Module\CompositeModuleAttributesProvider::extendModuleAttributes($base)->extendFromComposite($this);
    }

    public function addChild(\Nethgui\Module\ModuleInterface $module)
    {
        $this->plugins->addChild($module);
        return $this;
    }

    public function getChildren()
    {
        return $this->plugins->getChildren();
    }

    public function setPlatform(\Nethgui\System\PlatformInterface $platform)
    {
        parent::setPlatform($platform);
        $this->plugins->setPlatform($this->getPlatform());
        $this->innerAction->setPlatform($this->getPlatform());
        return $this;
    }

    public function initialize()
    {
        $this->innerAction->setParent($this->getParent());
        parent::initialize();
        $this->innerAction->initialize();
        $this->plugins->initialize();
        $this->plugins->loadChildrenDirectory($this->innerAction, $this->pluginsPath);
    }

    public function bind(\Nethgui\Controller\RequestInterface $request)
    {
        // pass-through $request to inner action:
        $this->innerAction->bind($request);

        // pass request subset to plugins
        $this->plugins->bind($request->spawnRequest($this->plugins->getIdentifier()));
    }

    public function validate(\Nethgui\Controller\ValidationReportInterface $report)
    {
        $this->innerAction->validate($report);
        $this->plugins->validate($report);
    }

    public function process()
    {
        $this->plugins->process();
        $this->innerAction->process();
    }

    public function prepareView(\Nethgui\View\ViewInterface $view)
    {        
        $this->innerAction->prepareView($view);
        $this->plugins->prepareView($view->spawnView($this->plugins, TRUE));
    }

    public function nextPath()
    {
        return $this->innerAction->nextPath();
    }

    public function asAuthorizationString()
    {
        return $this->innerAction->asAuthorizationString();
    }

    public function getAuthorizationAttribute($attributeName)
    {
        return $this->innerAction->getAuthorizationAttribute($attributeName);
    }

    public function setDependencyInjector($di)
    {
        $this->dependencyInjector = $di;
        $this->plugins->setDependencyInjector($di);
        return $this;
    }

}