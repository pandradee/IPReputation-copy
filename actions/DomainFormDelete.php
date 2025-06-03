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

use CControllerResponseData;

class DomainFormDelete extends BaseAction
{
    protected $post_content_type = self::TYPE_JSON;
    protected $request_method = self::POST;

    protected function checkInput()
    {
        $this->validateInput([
            'id' => 'string',
            'ids' => 'array'
        ]);

        return true;
    }

    protected function checkPermissions()
    {
        return parent::checkPermissions();
    }

    protected function doAction()
    {
        $ids = [];
        
        if ($this->hasInput('id')) {
            $ids[] = $this->getInput('id');
        }
        
        if ($this->hasInput('ids')) {
            $ids = array_merge($ids, $this->getInput('ids', []));
        }
        
        if (empty($ids)) {
            $data = [
                'success' => false,
                'error' => _('No domains selected for deletion')
            ];
            
            $response = new CControllerResponseData($data);
            $this->setResponse($response);
            return;
        }
        
        try {
            $deleted = 0;
            
            foreach ($ids as $id) {
                // Verificar se o domínio existe
                $domain = $this->module->storage->get(['id' => $id]);
                
                if ($domain) {
                    // Excluir o domínio
                    $this->module->storage->delete(['id' => $id]);
                    $deleted++;
                }
            }
            
            $data = [
                'success' => true,
                'message' => _n(
                    'Domain deleted successfully',
                    '%1$s domains deleted successfully',
                    $deleted,
                    $deleted
                )
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