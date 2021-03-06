<?php
namespace Nethgui\Module;

/*
 * Copyright (C) 2011 Nethesis S.r.l.
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
 * A ModuleSetInterface is a module instances collection
 * 
 * It caches module instances and allows iterating over all known modules
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 */
interface ModuleSetInterface extends \IteratorAggregate
{
    /**
     * @param string $moduleIdentifier
     * @return \Nethgui\Module\ModuleInterface The module instance
     */
    public function getModule($moduleIdentifier);
    
}

