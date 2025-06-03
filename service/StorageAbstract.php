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

use Exception;

abstract class StorageAbstract
{
    public abstract function get(array $filter): array;

    public abstract function create(array $messages): array;

    public abstract function update(array $messages): array;

    public abstract function delete(array $messages): array;

    /**
     * Define um item no armazenamento
     * 
     * @param array $data Dados a serem armazenados
     * @return array Dados armazenados
     */
    public abstract function set(array $data): array;

    /**
     * Method to initialize storage, is called by storage class contructor before setting $dirty flag.
     *
     * @throws Exception when initialization failed.
     */
    public abstract function begin();

    /**
     * Commit changes to storage.
     *
     * @throws Exception when commit changes failed.
     */
    public abstract function commit();

    /**
     * Initialize storage when module is installed. May return initializated configuration.
     */
    public abstract function setup(): array;
}
