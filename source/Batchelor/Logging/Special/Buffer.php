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

namespace Batchelor\Logging\Special;

use Batchelor\Logging\Target\Adapter;
use Batchelor\Logging\Logger;
use Batchelor\Logging\Writer;

/**
 * The message buffering logger.
 * 
 * This class masquerades for an log writer providing message buffering until
 * the script terminates. When terminating, all buffered messages are flushed
 * to the target writer.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Buffer extends Adapter implements Logger
{

        /**
         * The message writer.
         * @var Writer
         */
        private $_writer;
        /**
         * The message buffer.
         * @var array 
         */
        private $_buffer = [];

        /**
         * Constructor.
         * @param Writer $writer The shadowed writer.
         */
        public function __construct(Writer $writer)
        {
                $this->_writer = $writer;
        }

        /**
         * Destructor.
         */
        public function __destruct()
        {
                $this->flush();
        }

        /**
         * Flush buffered messages.
         */
        public function flush()
        {
                $this->write($this->_writer, $this->_buffer);
        }

        /**
         * Write buffered messages.
         * 
         * @param Logger $writer The message writer.
         * @param array $buffer The buffered messages.
         */
        private function write(Logger $writer, array &$buffer)
        {
                foreach ($buffer as $index => $entry) {
                        $writer->message($entry['priority'], $entry['message']['message']);
                        unset($buffer[$index]);
                }
        }

        /**
         * Check if buffer is empty.
         * @return bool
         */
        public function isEmpty(): bool
        {
                return count($this->_buffer) == 0;
        }

        /**
         * Get number of buffered messages.
         * @return int
         */
        public function getSize(): int
        {
                return count($this->_buffer);
        }

        /**
         * {@inheritdoc}
         */
        public function message(int $priority, string $message, array $args = array()): bool
        {
                $this->_buffer[] = [
                        'priority' => $priority,
                        'message'  => $this->_writer->getMessage($message, $args)
                ];
                return true;
        }

}
