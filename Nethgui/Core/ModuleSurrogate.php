<?php
/**
 * @package Core
 * @author Davide Principi <davide.principi@nethesis.it>
 */

/**
 * A Module surrogate is employed to store module informations into the User session,
 * during DialogBox serialization.
 * 
 * @see Nethgui_Core_DialogBox
 * @internal
 * @package Core
 */
class Nethgui_Core_ModuleSurrogate implements Nethgui_Core_ModuleInterface, Nethgui_Core_LanguageCatalogProvider, Serializable
{

    private $info;

    public function __construct(Nethgui_Core_ModuleInterface $originalModule)
    {
        $this->info = array();

        $this->info['getIdentifier'] = $originalModule->getIdentifier();
        $this->info['getTitle'] = $originalModule->getTitle();
        $this->info['getDescription'] = $originalModule->getDescription();
        $this->info['getLanguageCatalog'] = $originalModule->getLanguageCatalog();

        $parent = $originalModule->getParent();
        if ($parent instanceof Nethgui_Core_ModuleInterface) {
            $this->info['getParent'] = new self($parent);
        } else {
            $this->info['getParent'] = NULL;
        }
    }

    public function getDescription()
    {
        return $this->info['getDescription'];
    }

    public function getIdentifier()
    {
        return $this->info['getIdentifier'];
    }

    public function getParent()
    {
        return $this->info['getParent'];
    }

    public function getTitle()
    {
        return $this->info['getTitle'];
    }

    public function getLanguageCatalog()
    {
        return $this->info['getLanguageCatalog'];
    }

    public function initialize()
    {
        throw new Exception('Not implemented ' . __METHOD__);
    }

    public function isInitialized()
    {
        throw new Exception('Not implemented ' . __METHOD__);
    }

    public function prepareView(Nethgui_Core_ViewInterface $view, $mode)
    {
        throw new Exception('Not implemented ' . __METHOD__);
    }

    public function setHostConfiguration(Nethgui_Core_HostConfigurationInterface $hostConfiguration)
    {
        throw new Exception('Not implemented ' . __METHOD__);
    }

    public function setParent(Nethgui_Core_ModuleInterface $parentModule)
    {
        throw new Exception('Not implemented ' . __METHOD__);
    }

    public function serialize()
    {
        return serialize($this->info);
    }

    public function unserialize($serialized)
    {
        $this->info = unserialize($serialized);
    }

}