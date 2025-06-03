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
use Modules\IPReputation\Service\IPData;

class DomainForm extends BaseAction
{
    protected $post_content_type = self::TYPE_JSON;

    protected function checkInput()
    {
        $this->validateInput([
            'id' => 'string'
        ]);

        return true;
    }

    protected function checkPermissions()
    {
        return parent::checkPermissions();
    }

    protected function doAction()
    {
        $data = [
            'title' => $this->hasInput('id') ? _('Editar Domínio') : _('Novo Domínio'),
            'buttons' => [
                [
                    'title' => _('Adicionar'),
                    'class' => 'js-add',
                    'keepOpen' => true,
                    'isSubmit' => true,
                    'action' => 'domain.form.submit',
                    'disabled' => false
                ]
            ],
            'form' => $this->getForm(),
            'csrf_token' => $this->getActionCsrfToken('domain.form.submit')
        ];

        $response = new CControllerResponseData($data);
        $this->setResponse($response);
    }

    /**
     * Retorna os dados do formulário
     *
     * @return array
     */
    protected function getForm(): array
    {
        $domain = [
            'id' => '',
            'domain' => '',
            'description' => '',
            'owner' => '',
            'tags' => []
        ];

        if ($this->hasInput('id')) {
            $id = $this->getInput('id');
            $stored_domain = $this->module->storage->get(['id' => $id]);
            
            if ($stored_domain) {
                $domain = array_merge($domain, $stored_domain);
            }
        }

        return [
            'id' => $domain['id'],
            'domain' => $domain['domain'],
            'description' => $domain['description'],
            'owner' => $domain['owner'],
            'tags' => $domain['tags']
        ];
    }
} 