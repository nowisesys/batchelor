<?php

/*
 * Copyright (C) 2018 Anders Lövgren (Nowise Systems)
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

namespace Batchelor\Queue\Task\Execute;

/**
 * The process command interface.
 * 
 * @author Anders Lövgren (Nowise Systems)
 */
interface Selectable
{

        /**
         * Get command string.
         * @return string 
         */
        function getCommand(): string;

        /**
         * Get working directory.
         * @return string 
         */
        function getDirectory();

        /**
         * Get environment varibles.
         * @return array 
         */
        function getEnvironment();

        /**
         * On output ready for read.
         * 
         * Called when activity in stream connected to process stdout has been
         * detected, either closed or bytes available.
         * 
         * @param resource $stream The input stream.
         */
        function onOutput($stream);

        /**
         * On error ready for read.
         * 
         * Called when activity in stream connected to process stderr has been
         * detected, either closed or bytes available.
         * 
         * @param resource $stream The error stream.
         */
        function onError($stream);

        /**
         * On request for command input.
         * 
         * Called when activity in stream connected to process stdin has been
         * detected, either closed or bytes requested.
         * 
         * @param resource $stream The output stream.
         */
        function onInput($stream);
}
