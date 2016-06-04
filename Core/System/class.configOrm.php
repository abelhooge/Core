<?php
/**
 * FuzeWorks.
 *
 * The FuzeWorks MVC PHP FrameWork
 *
 * Copyright (C) 2015   TechFuze
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author    TechFuze
 * @copyright Copyright (c) 2013 - 2016, Techfuze. (http://techfuze.net)
 * @copyright Copyright (c) 1996 - 2015, Free Software Foundation, Inc. (http://www.fsf.org/)
 * @license   http://opensource.org/licenses/GPL-3.0 GPLv3 License
 *
 * @link  http://fuzeworks.techfuze.net
 * @since Version 0.0.1
 *
 * @version Version 0.0.1
 */

namespace FuzeWorks\ConfigORM;
use ConfigException;

/**
 * ORM class for config files in PHP files.
 *
 * Handles entries in the config directory of FuzeWorks and is able to dynamically update them when requested
 *
 * @author    Abel Hoogeveen <abel@techfuze.net>
 * @copyright Copyright (c) 2013 - 2016, Techfuze. (http://techfuze.net)
 * @todo      Implement unit tests
 */
class ConfigORM extends ConfigORMAbstract
{
    /**
     * The current filename.
     *
     * @var string filename
     */
    private $file;

    /**
     * Sets up the class and the connection to the PHP file.
     *
     * @param string $filename The current filename
     *
     * @throws ConfigException on fatal error
     */
    public function __construct($file = null)
    {
        if (is_null($file))
        {
            throw new ConfigException('Could not load config file. No file provided', 1);
        }
        elseif (file_exists($file)) 
        {
            $this->file = $file;
            $this->cfg = (array) include $file;
            $this->originalCfg = $this->cfg;
        } 
        else 
        {
            throw new ConfigException('Could not load config file. Config file does not exist', 1);
        }
    }

    /**
     * Updates the config file and writes it.
     *
     * @throws ConfigException on fatal error
     */
    public function commit()
    {
    	// Write the changes
        if (is_writable($this->file)) {
            $config = var_export($this->cfg, true);
            file_put_contents($this->file, "<?php return $config ;");

            return true;
        }
        throw new ConfigException("Could not write config file. $file is not writable", 1);
    }
}