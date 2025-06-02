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


/**
 * @var CPartial $this
 */

use Modules\IPReputation\Service\IPData;

(new CFilter())
    ->addVar('action', 'module.iprep.list')
    ->setResetUrl((new CUrl('zabbix.php'))->setArgument('action', 'module.iprep.list'))
    ->setProfile('module.iprep.filter')
    ->setActiveTab($data['filter_tab'])
    ->addFilterTab(_('Filter'), [
        (new CFormGrid())
            ->addClass(CFormGrid::ZBX_STYLE_FORM_GRID_LABEL_WIDTH_TRUE)
            ->addItem([
                new CLabel(_('Name'), 'filter_name'),
                new CFormField(
                    (new CTextBox('filter_name', $data['name']??''))
                        ->setWidth(ZBX_TEXTAREA_FILTER_STANDARD_WIDTH)
                        ->setAttribute('autofocus', 'autofocus')
                )
            ]),
        (new CFormGrid())
            ->addClass(CFormGrid::ZBX_STYLE_FORM_GRID_LABEL_WIDTH_TRUE)
            ->addItem([
                new CLabel(_('State')),
                new CFormField(
                    (new CRadioButtonList('filter_state', intval($data['state']??-1)))
                        ->addValue(_('Any'), -1)
                        ->addValue(_('Active'), IPData::STATE_ACTIVE)
                        ->addValue(_('Approaching'), IPData::STATE_APPROACHING)
                        ->addValue(_('Expired'), IPData::STATE_EXPIRED)
                        ->setModern(true)
                )
            ]),
        (new CFormGrid())
            ->addClass(CFormGrid::ZBX_STYLE_FORM_GRID_LABEL_WIDTH_TRUE)
            ->addItem([
                new CLabel(_('Status')),
                new CFormField(
                    (new CRadioButtonList('filter_status', intval($data['status']??-1)))
                        ->addValue(_('All'), -1)
                        ->addValue(_('Enabled'), IPData::STATUS_ENABLED)
                        ->addValue(_('Disabled'), IPData::STATUS_DISABLED)
                        ->setModern(true)
                )
            ]),
    ])->show();
