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
 * @var CView $this
 * @var array $data
 */

use Modules\IPReputation\Service\IPData;

$this->addJsFile('colorpicker.js');
$this->addJsFile('class.calendar.js');

$view_url = (new CUrl('zabbix.php'))
    ->setArgument('action', 'module.iprep.list')
    ->getUrl();

$form = (new CForm())
    ->removeId()
    ->setName('js-iprep-list');

$table = (new CTableInfo())
    ->setHeader([
        (new CColHeader(
            (new CCheckBox('all_iprep'))->onClick("checkAll('".$form->getName()."', 'all_iprep', 'ids');")
        ))->addClass(ZBX_STYLE_CELL_WIDTH),
        make_sorting_header(_('Name'), 'name', $data['sort'], $data['sortorder'], $view_url),
        make_sorting_header(_('Show since'), 'show_since', $data['sort'], $data['sortorder'], $view_url),
        make_sorting_header(_('Active since'), 'active_since', $data['sort'], $data['sortorder'], $view_url),
        make_sorting_header(_('Active till'), 'active_till', $data['sort'], $data['sortorder'], $view_url),
        _('Repeat'),
        _('Color'),
        _('Status'),
        _('Message')
    ]);

$state_label = [
    IPData::STATE_ACTIVE => [ZBX_STYLE_COLOR_POSITIVE, _('Active')],
    IPData::STATE_APPROACHING => [ZBX_STYLE_COLOR_WARNING, _('Approaching')],
    IPData::STATE_EXPIRED => [ZBX_STYLE_COLOR_NEGATIVE, _('Expired')]
];

$event = 'document.dispatchEvent(new CustomEvent("iprep.form.edit", {detail:{id:%1$d}}))';

foreach ($data['messages'] as $message) {
    $state = $message['status'] == IPData::STATUS_ENABLED
        ? ($state_label[$message['state']]??[ZBX_STYLE_GREY, _('Unknown')])
        : [ZBX_STYLE_GREY, _('Disabled')];
    
    // Determinar se tem repeat
    $repeat = ($message['repeat'] ?? IPData::REPEAT_DISABLED) == IPData::REPEAT_ENABLED ? _('Yes') : _('No');
    
    // Color indicator
    $color_indicator = (new CDiv())
        ->addClass('color-indicator')
        ->addStyle('width: 20px; height: 20px; background-color: #' . ($message['message_color'] ?: '1f65f4') . '; border-radius: 3px; display: inline-block;');

    $table->addRow([
        new CCheckBox('ids['.$message['id'].']', $message['id']),
        (new CLink($message['name'], '#'))->onClick(sprintf($event, $message['id'])),
        $message['show_since'] ? date(ZBX_DATE_TIME, $message['show_since']) : '-',
        date(ZBX_DATE_TIME, $message['active_since']),
        date(ZBX_DATE_TIME, $message['active_till']),
        $repeat,
        $color_indicator,
        (new CDiv($state[1]))->addClass($state[0]),
        $message['message']
    ]);
}


$form->addItem([$table, $data['paging']]);

$form->addItem(
    new CActionButtonList('action', 'ids', [
        'enable' => [
            'name' => _('Enable'),
            'attributes' => [
                'class' => ZBX_STYLE_BTN_ALT,
                'formaction' => 'iprep.form.enable'
            ],
            'csrf_token' => $data['csrf_token']['iprep.form.enable']
        ],
        'disable' => [
            'name' => _('Disable'),
            'attributes' => [
                'class' => ZBX_STYLE_BTN_ALT,
                'formaction' => 'iprep.form.disable'
            ],
            'csrf_token' => $data['csrf_token']['iprep.form.disable']
        ],
        'delete' => [
            'name' => _('Delete'),
            'attributes' => [
                'class' => ZBX_STYLE_BTN_ALT,
                'formaction' => 'iprep.form.delete',
            ],
            'csrf_token' => $data['csrf_token']['iprep.form.delete'],
            'confirm_singular' => _('Delete selected message?'),
            'confirm_plural' => _('Delete selected messages?')
        ]
    ], 'iprep')
);

(new CHtmlPage())
    ->addItem((new CDiv())->setId('iprep-list-page'))
    ->setTitle(_('IPData of the Day'))
    ->setControls(
        (new CTag('nav', true,
            (new CList())
                ->addItem(
                    (new CSubmitButton(_('Create message of the day')))
                        ->onClick('document.dispatchEvent(new CustomEvent("iprep.form.edit", {detail:{}}))')
                )
        ))->setAttribute('aria-label', _('Content controls'))
    )
    ->addItem(new CPartial('iprep.list.filter', $data['filter'] + [
        'active_tab' => 1,
        'groups_multiselect' => []
    ]))
    ->addItem($form)
    ->show();
