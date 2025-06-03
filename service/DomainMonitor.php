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

namespace Modules\IPReputation\Service;

use Exception;

class DomainMonitor
{
    /** @var StorageAbstract $storage */
    protected $storage;
    
    /** @var int $timeout Timeout em segundos para cada consulta DNS */
    protected $timeout = 3;

    /** @var array $blacklists Lista de serviços de blacklist para domínios */
    protected $blacklists = [
        'spamhaus' => [
            'name' => 'Spamhaus DBL',
            'url' => 'dbl.spamhaus.org',
            'active' => 1,
            'type' => 'domain'
        ],
        'surbl' => [
            'name' => 'SURBL',
            'url' => 'multi.surbl.org',
            'active' => 1,
            'type' => 'domain'
        ],
        'uribl' => [
            'name' => 'URIBL',
            'url' => 'multi.uribl.com',
            'active' => 1,
            'type' => 'domain'
        ],
        'barracuda' => [
            'name' => 'Barracuda',
            'url' => 'b.barracudacentral.org',
            'active' => 1,
            'type' => 'domain'
        ],
        'phishtank' => [
            'name' => 'PhishTank',
            'url' => 'checkurl.phishtank.com',
            'active' => 1,
            'type' => 'domain'
        ]
    ];

    /**
     * Construtor
     * 
     * @param StorageAbstract $storage Instância de armazenamento
     */
    public function __construct(StorageAbstract $storage)
    {
        $this->storage = $storage;
    }

    /**
     * Verifica um domínio em todas as blacklists ativas
     * 
     * @param string $id ID do domínio a ser verificado
     * @return array Resultado da verificação
     */
    public function checkDomain(string $id): array
    {
        $data = $this->storage->get(['id' => $id]);
        if (empty($data)) {
            throw new Exception('Domínio não encontrado: ' . $id);
        }
        
        $domain = $data['domain'];
        
        // Verificar se as blacklists já estão armazenadas
        $stored_blacklists = $this->storage->get(['type' => 'blacklists']);
        
        if (empty($stored_blacklists)) {
            // Se não houver blacklists armazenadas, usar a lista padrão
            $active_blacklists = $this->blacklists;
            
            // Armazenar as blacklists para uso futuro
            foreach ($this->blacklists as $id => $blacklist) {
                $this->storage->set([
                    'id' => $id,
                    'type' => 'blacklists',
                    'name' => $blacklist['name'],
                    'url' => $blacklist['url'],
                    'active' => $blacklist['active'],
                    'type' => $blacklist['type']
                ]);
            }
        } else {
            // Filtrar blacklists ativas para domínios
            $active_blacklists = array_filter($stored_blacklists, function($bl) {
                return isset($bl['type']) && $bl['type'] === 'domain' && $bl['active'] == 1;
            });
        }
        
        $results = [];
        foreach ($active_blacklists as $blacklist) {
            $results[$blacklist['id']] = $this->checkDomainInBlacklist($domain, $blacklist);
        }
        
        // Adicionar verificação em serviços adicionais
        $results['virustotal'] = $this->checkVirusTotal($domain);
        $results['securitytrails'] = $this->checkSecurityTrails($domain);
        $results['whois'] = $this->getWhoisInfo($domain);
        
        // Calcular pontuação de reputação
        $reputation_score = $this->calculateReputationScore($results);
        
        // Salvar resultados
        $this->storage->update([
            'id' => $id,
            'type' => 'status',
            'status' => [
                'timestamp' => time(),
                'results' => $results,
                'score' => $reputation_score,
                'category' => $this->getReputationCategory($reputation_score)
            ]
        ]);
        
        return [
            'domain' => $domain,
            'results' => $results,
            'score' => $reputation_score,
            'category' => $this->getReputationCategory($reputation_score),
            'timestamp' => time()
        ];
    }
    
    /**
     * Verifica um domínio em uma blacklist específica usando DNS
     * 
     * @param string $domain Nome de domínio a ser verificado
     * @param array $blacklist Dados da blacklist
     * @return array Resultado da verificação
     */
    protected function checkDomainInBlacklist(string $domain, array $blacklist): array
    {
        $lookup = $domain . '.' . $blacklist['url'];
        
        $result = [
            'listed' => false,
            'response' => '',
            'timestamp' => time(),
            'name' => $blacklist['name']
        ];
        
        // Verificar se o domínio está listado usando consulta DNS
        $dns_result = $this->dnsLookup($lookup);
        if ($dns_result !== false) {
            $result['listed'] = true;
            $result['response'] = $dns_result;
            
            // Para Spamhaus DBL, interpretar o código de resposta
            if ($blacklist['url'] === 'dbl.spamhaus.org' && preg_match('/127\.0\.0\.(\d+)/', $dns_result, $matches)) {
                $code = (int)$matches[1];
                switch ($code) {
                    case 2:
                        $result['category'] = 'spam domain';
                        break;
                    case 3:
                        $result['category'] = 'phishing domain';
                        break;
                    case 4:
                        $result['category'] = 'malware domain';
                        break;
                    case 9:
                        $result['category'] = 'botnet C&C domain';
                        break;
                    default:
                        $result['category'] = 'listed';
                }
            }
        }
        
        return $result;
    }
    
    /**
     * Realiza uma consulta DNS
     * 
     * @param string $lookup Nome a ser consultado
     * @return string|false Resultado da consulta ou false se não encontrado
     */
    protected function dnsLookup(string $lookup): string|false
    {
        // Configurar timeout para a consulta DNS
        $old_timeout = ini_get('default_socket_timeout');
        ini_set('default_socket_timeout', $this->timeout);
        
        $result = gethostbyname($lookup);
        
        // Restaurar timeout original
        ini_set('default_socket_timeout', $old_timeout);
        
        if ($result === $lookup) {
            return false;
        }
        
        return $result;
    }
    
    /**
     * Simula verificação no VirusTotal (em produção usaria a API)
     * 
     * @param string $domain Nome de domínio a ser verificado
     * @return array Resultado da verificação
     */
    protected function checkVirusTotal(string $domain): array
    {
        // Simulação - em produção usaria a API do VirusTotal
        $detected = mt_rand(0, 3);
        $engines = 68;
        
        return [
            'checked' => true,
            'detected_by' => $detected,
            'total_engines' => $engines,
            'score' => ($detected / $engines) * 100,
            'categories' => $detected > 0 ? ['suspicious'] : [],
            'timestamp' => time()
        ];
    }
    
    /**
     * Simula verificação no SecurityTrails (em produção usaria a API)
     * 
     * @param string $domain Nome de domínio a ser verificado
     * @return array Resultado da verificação
     */
    protected function checkSecurityTrails(string $domain): array
    {
        // Simulação - em produção usaria a API do SecurityTrails
        $age = mt_rand(30, 3650); // Idade do domínio em dias
        $suspicious = mt_rand(0, 1); // 0 = não suspeito, 1 = suspeito
        
        return [
            'checked' => true,
            'domain_age_days' => $age,
            'suspicious' => $suspicious,
            'dns_records' => mt_rand(3, 15),
            'subdomains_count' => mt_rand(0, 8),
            'timestamp' => time()
        ];
    }
    
    /**
     * Simula obtenção de informações WHOIS (em produção usaria API ou comando whois)
     * 
     * @param string $domain Nome de domínio
     * @return array Informações WHOIS
     */
    protected function getWhoisInfo(string $domain): array
    {
        // Simulação - em produção usaria API ou comando whois
        $registrars = ['GoDaddy', 'Namecheap', 'Google Domains', 'Amazon Registrar', 'Network Solutions'];
        $tlds = ['com', 'org', 'net', 'io', 'br'];
        
        $tld = explode('.', $domain);
        $tld = end($tld);
        
        if (!in_array($tld, $tlds)) {
            $tld = 'com';
        }
        
        $creation_date = time() - (mt_rand(30, 3650) * 86400);
        
        return [
            'registrar' => $registrars[array_rand($registrars)],
            'creation_date' => $creation_date,
            'expiration_date' => $creation_date + (mt_rand(1, 5) * 31536000), // 1-5 anos
            'name_servers' => ['ns1.' . $domain, 'ns2.' . $domain],
            'status' => ['clientTransferProhibited'],
            'tld' => $tld,
            'timestamp' => time()
        ];
    }
    
    /**
     * Calcula um score de reputação com base nos resultados
     * 
     * @param array $results Resultados das verificações
     * @return int Score de reputação (0-100, quanto maior, pior a reputação)
     */
    protected function calculateReputationScore(array $results): int
    {
        $score = 0;
        $dnsbl_count = 0;
        $listed_count = 0;
        
        // Contar blacklists que listaram o domínio
        foreach ($results as $key => $result) {
            if (!in_array($key, ['virustotal', 'securitytrails', 'whois'])) {
                $dnsbl_count++;
                if (isset($result['listed']) && $result['listed']) {
                    $listed_count++;
                    
                    // Spamhaus tem peso maior
                    if ($key === 'spamhaus') {
                        $score += 25;
                    } else {
                        $score += 15; // Cada listagem em DNSBL adiciona 15 pontos
                    }
                }
            }
        }
        
        // Adicionar pontuação do VirusTotal
        if (isset($results['virustotal']['score'])) {
            $score += $results['virustotal']['score'] * 0.3; // 30% de peso para VirusTotal
        }
        
        // Adicionar pontuação do SecurityTrails
        if (isset($results['securitytrails'])) {
            // Domínios novos são mais suspeitos (menos de 90 dias)
            if (isset($results['securitytrails']['domain_age_days']) && $results['securitytrails']['domain_age_days'] < 90) {
                $score += (90 - $results['securitytrails']['domain_age_days']) / 3;
            }
            
            // Domínios marcados como suspeitos
            if (isset($results['securitytrails']['suspicious']) && $results['securitytrails']['suspicious']) {
                $score += 15;
            }
        }
        
        // Adicionar pontuação baseada em WHOIS
        if (isset($results['whois'])) {
            // Domínios novos são mais suspeitos (menos de 90 dias)
            if (isset($results['whois']['creation_date'])) {
                $domain_age_days = (time() - $results['whois']['creation_date']) / 86400;
                if ($domain_age_days < 90) {
                    $score += (90 - $domain_age_days) / 3;
                }
            }
        }
        
        // Garantir que o score está entre 0 e 100
        return min(100, max(0, (int)$score));
    }
    
    /**
     * Obtém a categoria de reputação com base no score
     * 
     * @param int $score Score de reputação
     * @return string Categoria de reputação
     */
    protected function getReputationCategory(int $score): string
    {
        if ($score >= 80) {
            return 'Alto Risco';
        } elseif ($score >= 50) {
            return 'Médio Risco';
        } elseif ($score >= 20) {
            return 'Baixo Risco';
        } else {
            return 'Seguro';
        }
    }
} 