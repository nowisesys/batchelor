<?php

use Batchelor\Queue\WorkDirectory;
use Batchelor\Render\Queue;
use Batchelor\WebService\Types\JobIdentity;
use UUP\Site\Page\Service\StandardService;
use UUP\Site\Request\Params;

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

/**
 * The 
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class DownloadPage extends StandardService
{

        public function render()
        {
                $this->sendFile(
                    (new Queue(null))->getReader(), $this->params
                );
        }

        private function sendFile(WorkDirectory $reader, Params $params)
        {
                $ident = JobIdentity::create([
                            'jobid'  => $params->getParam("jobid"),
                            'result' => $params->getParam("result")
                ]);
                $reader->getContent($ident, $params->getParam("name"), true);
        }

}
