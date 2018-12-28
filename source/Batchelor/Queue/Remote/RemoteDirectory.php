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

namespace Batchelor\Queue\Remote;

use Batchelor\Queue\WorkDirectory;
use Batchelor\WebService\Client\JsonClientHandler;
use Batchelor\WebService\Types\File;
use Batchelor\WebService\Types\JobIdentity;

/**
 * The remote queue directory.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class RemoteDirectory implements WorkDirectory
{

        /**
         * The remote client.
         * @var JsonClientHandler 
         */
        private $_client;

        /**
         * Constructor.
         * @param JsonClientHandler $client The remote client.
         */
        public function __construct(JsonClientHandler $client)
        {
                $this->_client = $client;
        }

        /**
         * {@inheritdoc}
         */
        public function getContent(JobIdentity $job, string $file, bool $send = false)
        {
                return base64_decode(
                    $this->_client
                        ->callMethod("fopen", [
                                'job'  => $job,
                                'file' => $file,
                                'send' => false         // Always return content
                        ])
                );
        }

        /**
         * {@inheritdoc}
         */
        public function getFiles(JobIdentity $job, array $result = [])
        {
                $remote = $this->_client
                    ->callMethod("readdir", (array) $job);

                foreach ($remote as $file) {
                        $result[] = new File(...array_values($file));
                }

                return $result;
        }

        /**
         * {@inheritdoc}
         */
        public function getJobs()
        {
                return $this->_client
                        ->callMethod("opendir", []);
        }

}
