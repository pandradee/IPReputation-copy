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

use API;
use CUrl;
use CArrayHelper;
use CProfile;
use CPagerHelper;
use CControllerResponseData;
use Modules\IPReputation\Service\DomainMonitor;

class DomainDashboard extends BaseAction
{
    protected function checkInput()
    {
        $this->validateInput([
            'filter_set'        => 'in 1',
            'filter_rst'        => 'in 1',
            'filter_domain'     => 'string',
            'filter_score'      => 'int',
            'filter_category'   => 'string',
            'sort'              => 'in name,domain,score,category,last_check',
            'sortorder'         => 'in '.implode(',', [ZBX_SORT_UP, ZBX_SORT_DOWN]),
            'page'              => 'ge 1'
        ]);

        return true;
    }

    protected function doAction()
    {
        try {
            if ($this->hasInput('filter_set')) {
                $this->updateProfiles();
            }
            elseif ($this->hasInput('filter_rst')) {
                $this->deleteProfiles();
            }

            $filter = $this->getFilter();
            
            // Obter dados dos domínios
            try {
                $domains = $this->module->storage->get([
                    'type' => 'domain'
                ]) ?: [];
            } catch (\Exception $e) {
                // Log do erro para um arquivo
                file_put_contents(
                    __DIR__ . '/../var/error_log.txt',
                    date('Y-m-d H:i:s') . ' - Error getting domains: ' . $e->getMessage() . "\n",
                    FILE_APPEND
                );
                $domains = [];
            }
            
            // Filtrar domínios se necessário
            if (!empty($filter['domain'])) {
                $domains = array_filter($domains, function($domain) use ($filter) {
                    return stripos($domain['domain'], $filter['domain']) !== false;
                });
            }
            
            if (!empty($filter['score']) && $filter['score'] > 0) {
                $domains = array_filter($domains, function($domain) use ($filter) {
                    if (isset($domain['status']) && isset($domain['status']['score'])) {
                        return $domain['status']['score'] >= $filter['score'];
                    }
                    return false;
                });
            }
            
            if (!empty($filter['category'])) {
                $domains = array_filter($domains, function($domain) use ($filter) {
                    if (isset($domain['status']) && isset($domain['status']['category'])) {
                        return $domain['status']['category'] === $filter['category'];
                    }
                    return false;
                });
            }
            
            // Preparar dados para exibição
            $data = [
                'sort' => $this->getInput('sort', 'domain'),
                'sortorder' => $this->getInput('sortorder', ZBX_SORT_UP)
            ];
            
            // Ordenar os domínios
            if ($data['sort'] === 'score' || $data['sort'] === 'category' || $data['sort'] === 'last_check') {
                usort($domains, function($a, $b) use ($data) {
                    $field = $data['sort'];
                    
                    if ($field === 'score') {
                        $a_val = isset($a['status']) && isset($a['status']['score']) ? $a['status']['score'] : 0;
                        $b_val = isset($b['status']) && isset($b['status']['score']) ? $b['status']['score'] : 0;
                    } 
                    elseif ($field === 'category') {
                        $a_val = isset($a['status']) && isset($a['status']['category']) ? $a['status']['category'] : '';
                        $b_val = isset($b['status']) && isset($b['status']['category']) ? $b['status']['category'] : '';
                    }
                    elseif ($field === 'last_check') {
                        $a_val = isset($a['status']) && isset($a['status']['timestamp']) ? $a['status']['timestamp'] : 0;
                        $b_val = isset($b['status']) && isset($b['status']['timestamp']) ? $b['status']['timestamp'] : 0;
                    }
                    
                    if ($a_val == $b_val) {
                        return 0;
                    }
                    
                    if ($data['sortorder'] == ZBX_SORT_UP) {
                        return $a_val > $b_val ? 1 : -1;
                    } else {
                        return $a_val < $b_val ? 1 : -1;
                    }
                });
            } else {
                // Ordenação padrão por nome de domínio
                CArrayHelper::sort($domains, [['field' => 'domain', 'order' => $data['sortorder']]]);
            }
            
            // Paginação
            $paging = CPagerHelper::paginate($this->getInput('page', 1), $domains, $data['sortorder'],
                (new CUrl('zabbix.php'))->setArgument('action', $this->getAction())
            );
            
            // Preparar categorias para filtro
            $categories = [
                'Alto Risco',
                'Médio Risco',
                'Baixo Risco',
                'Seguro'
            ];
            
            $data += [
                'filter' => $filter,
                'paging' => $paging,
                'domains' => $domains,
                'categories' => $categories,
                'csrf_token' => [
                    'domain.check' => $this->getActionCsrfToken('domain.check'),
                    'domain.form.edit' => $this->getActionCsrfToken('domain.form.edit'),
                    'domain.form.delete' => $this->getActionCsrfToken('domain.form.delete')
                ],
            ];

            $response = new CControllerResponseData($data);
            $response->setTitle(_('Monitor de BlackList de Domínios'));
            $this->setResponse($response);
        } catch (\Exception $e) {
            // Log do erro para um arquivo
            file_put_contents(
                __DIR__ . '/../var/error_log.txt',
                date('Y-m-d H:i:s') . ' - Error in doAction: ' . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n\n",
                FILE_APPEND
            );
            
            // Preparar uma resposta amigável
            $error_data = [
                'error' => true,
                'message' => _('Ocorreu um erro ao processar a solicitação.'),
                'details' => $e->getMessage()
            ];
            
            // Criar uma resposta com informações de erro
            $error_response = new CControllerResponseData($error_data);
            $error_response->setTitle(_('Erro - Monitor de BlackList de Domínios'));
            $this->setResponse($error_response);
        }
    }

    protected function updateProfiles()
    {
        CProfile::update('module.iprep.filter_domain', $this->getInput('filter_domain', ''), PROFILE_TYPE_STR);
        CProfile::update('module.iprep.filter_score', $this->getInput('filter_score', 0), PROFILE_TYPE_INT);
        CProfile::update('module.iprep.filter_category', $this->getInput('filter_category', ''), PROFILE_TYPE_STR);
    }

    protected function deleteProfiles()
    {
        CProfile::deleteIdx('module.iprep.filter_domain');
        CProfile::deleteIdx('module.iprep.filter_score');
        CProfile::deleteIdx('module.iprep.filter_category');
    }

    protected function getFilter(): array
    {
        return [
            'domain' => CProfile::get('module.iprep.filter_domain', ''),
            'score' => CProfile::get('module.iprep.filter_score', 0),
            'category' => CProfile::get('module.iprep.filter_category', ''),
            'filter_profile' => 'module.iprep.filter',
            'filter_tab' => CProfile::get('module.iprep.filter.active', 1)
        ];
    }
} 