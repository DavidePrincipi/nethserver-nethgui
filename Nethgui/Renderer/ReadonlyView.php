<?php
/**
 * @package Nethgui
 * @subpackage Core
 * @author Davide Principi <davide.principi@nethesis.it>
 * @ignore
 */

/**
 * Disable write access operations of a view.
 *
 * @package Nethgui
 * @subpackage Core
 * @author Davide Principi <davide.principi@nethesis.it>
 * @ignore
 */
class Nethgui_Renderer_ReadonlyView implements Nethgui_Core_ViewInterface, Nethgui_Log_LogConsumerInterface
{

    /**
     * @var Nethgui_Core_ViewInterface
     */
    protected $view;

    public function __construct(Nethgui_Core_ViewInterface $view)
    {
        if ($view instanceof self) {
            // Prevent re-wrapping of a read-only view instance:
            $this->view = $view->view;
        } else {
            $this->view = $view;
        }
    }

    public function copyFrom($data)
    {
        throw new Nethgui_Exception_View('Cannot change the view values');
    }

    public function getIterator()
    {
        return $this->view->getIterator();
    }

    public function getModule()
    {
        return $this->view->getModule();
    }

    public function offsetExists($offset)
    {
        return $this->view->offsetExists($offset);
    }

    public function offsetGet($offset)
    {
        return $this->view->offsetGet($offset);
    }

    public function offsetSet($offset, $value)
    {
        throw new Nethgui_Exception_View('Cannot change the view value');
    }

    public function offsetUnset($offset)
    {
        throw new Nethgui_Exception_View('Cannot unset a view value');
    }

    public function setTemplate($template)
    {
        throw new Nethgui_Exception_View('Cannot change the view template');
    }

    public function getTemplate()
    {
        return $this->view->getTemplate();
    }

    public function spawnView(Nethgui_Core_ModuleInterface $module, $register = FALSE)
    {
        throw new Nethgui_Exception_View('Readonly view: cannot spawn another view!');
    }

    public function translate($message, $args = array())
    {
        return $this->view->translate($message, $args);
    }

    public function getTranslator()
    {
        return $this->view->getTranslator();
    }

    public function getModulePath()
    {
        return $this->view->getModulePath();
    }

    public function getUniqueId($parts = '')
    {
        return $this->view->getUniqueId($parts);
    }

    public function getClientEventTarget($name)
    {
        return $this->view->getClientEventTarget($name);
    }

    public function getModuleUrl($path = '')
    {
        return $this->view->getModuleUrl($path);
    }

    public function setLog(Nethgui_Log_AbstractLog $log)
    {
        throw new Exception(sprintf('Cannot invoke setLog() on %s', get_class($this)));
    }

    public function getLog()
    {
        if ($this->view instanceof Nethgui_Log_LogConsumerInterface) {
            return $this->view->getLog();
        } else {
            return new Nethgui_Log_Nullog();
        }
    }

    public function createUiCommand($methodName, $arguments)
    {
        return $this->view->createUiCommand($methodName, $arguments);
    }
    
    public function resolvePath($path)
    {
        return $this->view->resolvePath($path);
    }
}