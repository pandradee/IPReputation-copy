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

class DomainFormSubmit extends BaseAction
{
    protected $post_content_type = self::TYPE_JSON;
    protected $request_method = self::POST;

    protected function checkInput()
    {
        $this->validateInput([
            'id' => 'string',
            'domain' => 'required|string|not_empty',
            'description' => 'string',
            'owner' => 'string',
            'tags' => 'array'
        ]);

        return true;
    }

    protected function checkPermissions()
    {
        return parent::checkPermissions();
    }

    protected function doAction()
    {
        $id = $this->getInput('id', '');
        $domain = $this->getInput('domain');
        $description = $this->getInput('description', '');
        $owner = $this->getInput('owner', '');
        $tags = $this->getInput('tags', []);
        
        // Validar domínio
        if (!$this->isValidDomain($domain)) {
            $data = [
                'success' => false,
                'error' => _('Domínio inválido. Forneça um nome de domínio válido.')
            ];
            
            $response = new CControllerResponseData($data);
            $this->setResponse($response);
            return;
        }
        
        // Verificar se o domínio já existe
        if (empty($id)) {
            $existing_domains = $this->module->storage->get(['type' => 'domain']);
            
            foreach ($existing_domains as $existing) {
                if (isset($existing['domain']) && $existing['domain'] === $domain) {
                    $data = [
                        'success' => false,
                        'error' => _('Este domínio já está cadastrado.')
                    ];
                    
                    $response = new CControllerResponseData($data);
                    $this->setResponse($response);
                    return;
                }
            }
        }
        
        try {
            // Gerar ID único se necessário
            if (empty($id)) {
                $id = uniqid('domain_');
            }
            
            // Preparar dados para salvar
            $domain_data = [
                'id' => $id,
                'type' => 'domain',
                'domain' => $domain,
                'description' => $description,
                'owner' => $owner,
                'tags' => $tags,
                'added' => time()
            ];
            
            // Salvar no armazenamento
            $this->module->storage->set($domain_data);
            
            $data = [
                'success' => true,
                'message' => _('Domínio salvo com sucesso.'),
                'data' => $domain_data
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
    
    /**
     * Verifica se um domínio é válido
     * 
     * @param string $domain Domínio a ser verificado
     * @return bool True se o domínio for válido
     */
    protected function isValidDomain(string $domain): bool
    {
        // Expressão regular para validar domínios
        $pattern = '/^(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z0-9][a-z0-9-]{0,61}[a-z0-9]$/i';
        
        return (bool) preg_match($pattern, $domain);
    }
} 