<?php declare(strict_types = 0);
/*
** Copyright (C) 2025 Monzphere - Fork mantido por Monzphere
**
** This program is free software: you can redistribute it and/or modify it under the terms of
** the GNU Affero General Public License as published by the Free Software Foundation, version 3.
**
** This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
** without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
** See the GNU Affero General Public License for more details.
**
** You should have received a copy of the GNU Affero General Public License along with this program.
** If not, see <https://www.gnu.org/licenses/>.
**/


namespace Modules\IPReputation\Actions;

use CController;
use CControllerResponseData;
use Modules\IPReputation\Service\DomainMonitor;

class DomainCheck extends BaseAction
{
    protected $post_content_type = self::TYPE_JSON;
    protected $request_method = self::POST;

    protected function checkInput()
    {
        $this->validateInput([
            'id' => 'required|string'
        ]);

        return true;
    }

    protected function checkPermissions()
    {
        return parent::checkPermissions();
    }

    protected function doAction()
    {
        try {
            $id = $this->getInput('id');
            
            // Criar instância do monitor de domínio
            $monitor = new DomainMonitor($this->module->storage);
            
            // Verificar o domínio
            $result = $monitor->checkDomain($id);
            
            $data = [
                'success' => true,
                'data' => $result
            ];
        } catch (\Exception $e) {
            $data = [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
        
        $response = new CControllerResponseData($data);
        $this->setResponse($response);
    }
} 