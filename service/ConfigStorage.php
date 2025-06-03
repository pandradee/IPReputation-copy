<?php declare(strict_types = 0);
/*
** Copyright (C) 2001-2024 initMAX s.r.o.
** Copyright (C) 2024 Monzphere - Fork mantido por Monzphere
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

use APP;
use Exception;
use ModuleManager;

class ConfigStorage extends FileStorage
{
    protected array $messages = [];
    protected bool $dirty = false;

    public function __construct(array $config)
    {
        $this->messages = $config['data']??[];
    }

    public function begin()
    {
        if (!is_array($this->messages)) {
            throw new Exception('Failed to initialize file storage.');
        }
    }

    public function commit()
    {
        if ($this->dirty !== true) {
            return;
        }

        $state = false;
        /** @var \ModuleManager $manager */
        $manager = APP::ModuleManager();
        /** @var \Modules\IPReputation\Module $module */
        $module = $manager->getActionModule();

        if ($module) {
            $config = $module->getConfig();
            $config['storage']['data'] = $this->messages;
            $module->setConfig($config);
            $state = true;
        }

        if (!$state) {
            throw new Exception('Failed to commit messages storage.');
        }

        $this->dirty = false;
    }

    public function setup(): array
    {
        return [];
    }

    /**
     * Define um item no armazenamento
     * 
     * @param array $data Dados a serem armazenados
     * @return array Dados armazenados
     */
    public function set(array $data): array
    {
        if (!isset($data['id'])) {
            throw new Exception('ID is required for storage');
        }
        
        $this->messages[$data['id']] = $data;
        $this->dirty = true;
        
        return $data;
    }

    /**
     * Deleta um item do armazenamento
     * 
     * @param array $data Dados do item a ser excluído (contém 'id')
     * @return array Dados do item excluído
     */
    public function delete(array $data): array
    {
        if (!isset($data['id'])) {
            throw new Exception('ID is required for deletion');
        }
        
        if (isset($this->messages[$data['id']])) {
            $deleted = $this->messages[$data['id']];
            unset($this->messages[$data['id']]);
            $this->dirty = true;
            return $deleted;
        }
        
        return $data;
    }

    /**
     * Atualiza um item no armazenamento
     * 
     * @param array $data Dados a serem atualizados
     * @return array Dados atualizados
     */
    public function update(array $data): array
    {
        if (!isset($data['id'])) {
            throw new Exception('ID is required for update');
        }
        
        if (isset($this->messages[$data['id']])) {
            if (isset($data['status'])) {
                $this->messages[$data['id']]['status'] = $data['status'];
                $this->dirty = true;
            } elseif (isset($data['type']) && $data['type'] === 'status') {
                $this->messages[$data['id']]['status'] = $data['status'];
                $this->dirty = true;
            } else {
                $this->messages[$data['id']] = array_merge($this->messages[$data['id']], $data);
                $this->dirty = true;
            }
        } else {
            // Se não existir, criar novo
            return $this->set($data);
        }
        
        return $this->messages[$data['id']];
    }
}
