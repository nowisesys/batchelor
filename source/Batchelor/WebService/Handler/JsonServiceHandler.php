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

namespace Batchelor\WebService\Handler;

use BadMethodCallException;
use Batchelor\WebService\Common\ServiceBackend;
use Batchelor\WebService\Types\JobData;
use Batchelor\WebService\Types\JobIdentity;
use Batchelor\WebService\Types\QueueFilterResult;
use Batchelor\WebService\Types\QueueSortResult;

/**
 * The JSON service handler.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class JsonServiceHandler
{

        public function process(string $func, array $data = null)
        {
                if (method_exists($this, $func)) {
                        return call_user_func([$this, $func], $data);
                } elseif (empty($func)) {
                        throw new BadMethodCallException("The method name is empty");
                } else {
                        throw new BadMethodCallException("The method $func is missing");
                }
        }

        private function dequeue(array $data)
        {
                return (new ServiceBackend())
                        ->dequeue(JobIdentity::create($data));
        }

        private function enqueue(array $data)
        {
                return (new ServiceBackend())
                        ->enqueue(JobData::create($data));
        }

        private function fopen(array $data)
        {
                if (!isset($data['send'])) {
                        $data['send'] = true;
                }
                return (new ServiceBackend())
                        ->fopen(JobIdentity::create($data['job']), $data['file'], $data['send']);
        }

        private function opendir(array $data)
        {
                return (new ServiceBackend())
                        ->opendir();
        }

        private function queue(array $data)
        {
                return (new ServiceBackend())
                        ->queue(QueueSortResult::create($data), QueueFilterResult::create($data)
                );
        }

        private function readdir(array $data)
        {
                return (new ServiceBackend())
                        ->readdir(JobIdentity::create($data));
        }

        private function resume(array $data)
        {
                return (new ServiceBackend())
                        ->resume(JobIdentity::create($data));
        }

        private function select(array $data)
        {
                return (new ServiceBackend())
                        ->select($data['queue']);
        }

        private function stat(array $data)
        {
                return (new ServiceBackend())
                        ->stat(JobIdentity::create($data));
        }

        private function suspend(array $data)
        {
                return (new ServiceBackend())
                        ->suspend(JobIdentity::create($data));
        }

        private function version(array $data)
        {
                return (new ServiceBackend())
                        ->version();
        }

        private function watch(array $data)
        {
                return (new ServiceBackend())
                        ->watch($data['stamp']);
        }

}
