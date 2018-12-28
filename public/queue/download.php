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

use Batchelor\Controller\Standard\FileService;
use Batchelor\Queue\WorkDirectory;
use Batchelor\Render\Queue;
use Batchelor\WebService\Types\JobIdentity;
use UUP\Site\Request\Params;

/**
 * The file download.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class DownloadPage extends FileService
{

        protected function onRendering(): array
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

                // 
                // Local files are sent direct, but remote file needs proxying:
                // 
                if (($result = $reader->getContent($ident, $params->getParam("name"), true))) {
                        $this->sendHeaders($params);
                        $this->sendResult($result);
                }
        }

        private function sendHeaders(Params $params)
        {
                foreach ($this->getHeaders($params) as $key => $val) {
                        header("$key: $val");
                }
        }

        private function sendResult(string $result)
        {
                echo $result;
                exit(0);
        }

        private function getHeaders(Params $params): array
        {
                return [
                        'Content-Disposition' => sprintf('attachment; filename="%s"', basename($params->getParam("name"))),
                        'Content-Type'        => $params->getParam("mime"),
                        'Content-Length'      => $params->getParam("size")
                ];
        }

}
