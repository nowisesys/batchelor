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

namespace Batchelor\Logging\Target;

use Batchelor\Logging\Logger;

/**
 * The simple logger.
 *
 * Collects all logged messages in an array grouped by priority. Each message 
 * contains an timestamp and the message.
 * 
 * @author Anders Lövgren (Nowise Systems)
 */
class Simple extends Adapter implements Logger
{

        /**
         * The message buffer.
         * @var array 
         */
        private $_messages = [];

        /**
         * {@inheritdoc}
         */
        protected function doLogging(int $priority, string $message, array $args = array()): bool
        {
                $this->_messages[$priority][] = $this->getMessage($message, $args);
                return true;
        }

        /**
         * Get all messages.
         * @return array The logged messages.
         */
        public function getMessages(): array
        {
                return $this->_messages;
        }

}
