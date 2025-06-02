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


use Modules\IPReputation\Service\IPData;

/**
 * @var CView $this
 * @var array $data
 */

$form = (new CFormGrid())->addClass('iprep-config-form');
$action = 'document.dispatchEvent(new CustomEvent("%1$s", {detail:{button: this, overlay: arguments[0]}}))';
$js_data = [];
$switcher = [];

if ($data['id']) {
    $form->addItem(new CVar('id', $data['id']));

    $buttons = [
        [
            'title' => _('Update'),
            'class' => '',
            'keepOpen' => true,
            'isSubmit' => true,
            'action' => sprintf($action, 'iprep.form.submit'),
            'mvc_action' => 'iprep.form.submit'
        ],
        [
            'title' => _('Delete'),
            'confirmation' => _('Delete selected message?'),
            'class' => 'btn-alt',
            'keepOpen' => true,
            'isSubmit' => false,
            'action' => sprintf($action, 'iprep.form.delete'),
            'mvc_action' => 'iprep.form.delete'
        ]
    ];
}
else {
    $buttons = [
        [
            'title' => _('Add'),
            'class' => '',
            'keepOpen' => true,
            'isSubmit' => true,
            'action' => sprintf($action, 'iprep.form.submit'),
            'mvc_action' => 'iprep.form.submit'
        ]
    ];
}

$form->addItem([
    new CSpan(''),
    (new CImg($this->getAssetsPath() . '/img/monzphere.png'))
        ->addClass('monzphere-logo'),
]);


$form->addItem([
    new CLabel(_('Enabled'), 'status'),
    new CFormField((new CCheckBox('status', IPData::STATUS_ENABLED))
        ->setChecked($data['status'] == IPData::STATUS_ENABLED)
    )
]);

$form->addItem([
    (new CLabel(_('Name'), 'name'))->setAsteriskMark(),
    new CFormField((new CTextBox('name', $data['name']))
        ->setWidth(ZBX_TEXTAREA_STANDARD_WIDTH)
        ->setAriaRequired()
        ->setAttribute('autofocus', 'autofocus')
)]);

$form->addItem([
    new CLabel(_('Show since'), 'show_since'),
    new CFormField([
        (new CDateSelector('show_since', $data['show_since'] === '' ? '' : date(ZBX_DATE_TIME, $data['show_since'])))
            ->setDateFormat(ZBX_DATE_TIME)
            ->setPlaceholder(ZBX_DATE_TIME)
            ->addClass(ZBX_STYLE_FORM_INPUT_MARGIN)
            ->setEnabled(true),
        (new CColor('show_since_color', $data['show_since_color']))
            ->setEnabled(true),
    ])
]);

$form->addItem([
    (new CLabel(_('Active since'), 'active_since'))->setAsteriskMark(),
    new CFormField([
        (new CDateSelector('active_since', date(ZBX_DATE_TIME, $data['active_since'])))
            ->setDateFormat(ZBX_DATE_TIME)
            ->setPlaceholder(ZBX_DATE_TIME)
            ->setAriaRequired()
            ->addClass(ZBX_STYLE_FORM_INPUT_MARGIN),
        (new CColor('active_since_color', $data['active_since_color']))
            ->setEnabled(true),
    ])
]);


$form->addItem([
    (new CLabel(_('Active till'), 'active_till'))->setAsteriskMark(),
    new CFormField([
        (new CDateSelector('active_till', date(ZBX_DATE_TIME, $data['active_till'])))
            ->setDateFormat(ZBX_DATE_TIME)
            ->setPlaceholder(ZBX_DATE_TIME)
            ->setAriaRequired()
            ->addClass(ZBX_STYLE_FORM_INPUT_MARGIN),
        (new CColor('active_till_color', $data['active_till_color']))
            ->setEnabled(true),
    ])
]);


$switcher['repeat'] = [IPData::REPEAT_DISABLED => [], IPData::REPEAT_ENABLED => ['repeat_interval', 'repeat_frequency', 'repeat_end_type', 'repeat_end_date', 'repeat_end_count']];
$switcher['repeat_end_type'] = [
    IPData::REPEAT_END_NEVER => [], 
    IPData::REPEAT_END_DATE => ['repeat_end_date'], 
    IPData::REPEAT_END_COUNT => ['repeat_end_count']
];
$form->addItem([
    new CLabel(_('Repeat'), 'repeat'),
    new CFormField((new CRadioButtonList('repeat', (int) $data['repeat']))
        ->addValue(_('No'), IPData::REPEAT_DISABLED)
        ->addValue(_('Yes'), IPData::REPEAT_ENABLED)
        ->setEnabled(true)
        ->setModern()
    )
]);


$form->addItem([
    new CLabel(_('Repeat every'), 'repeat_frequency'),
    new CFormField([
        (new CNumericBox('repeat_frequency', $data['repeat_frequency'] ?: 1, 2))
            ->setWidth(ZBX_TEXTAREA_TINY_WIDTH),
        (new CSelect('repeat_interval'))
            ->setValue($data['repeat_interval'])
            ->addOption(new CSelectOption(IPData::REPEAT_DAY, _('Day(s)')))
            ->addOption(new CSelectOption(IPData::REPEAT_WEEK, _('Week(s)')))
            ->addOption(new CSelectOption(IPData::REPEAT_MONTH, _('Month(s)')))
            ->addOption(new CSelectOption(IPData::REPEAT_YEAR, _('Year(s)')))
    ])
]);

$form->addItem([
    new CLabel(_('End repeat'), 'repeat_end_type'),
    new CFormField([
        (new CRadioButtonList('repeat_end_type', (int) $data['repeat_end_type']))
            ->addValue(_('Never'), IPData::REPEAT_END_NEVER)
            ->addValue(_('On date'), IPData::REPEAT_END_DATE)
            ->addValue(_('After occurrences'), IPData::REPEAT_END_COUNT)
            ->setModern()
    ])
]);

$form->addItem([
    new CLabel(_('End date'), 'repeat_end_date'),
    new CFormField(
        (new CDateSelector('repeat_end_date', $data['repeat_end_date'] ? date(ZBX_DATE_TIME, $data['repeat_end_date']) : ''))
            ->setDateFormat(ZBX_DATE_TIME)
            ->setPlaceholder(ZBX_DATE_TIME)
    )
]);

$form->addItem([
    new CLabel(_('Occurrences'), 'repeat_end_count'),
    new CFormField(
        (new CNumericBox('repeat_end_count', $data['repeat_end_count'] ?: 1, 3))
            ->setWidth(ZBX_TEXTAREA_TINY_WIDTH)
    )
]);

$form->addItem([
    new CLabel(_('IPData bar color'), 'message_color'),
    new CFormField(
        (new CColor('message_color', $data['message_color']))
            ->setEnabled(true)
    )
]);

$form->addItem([
    (new CLabel(_('Message')))->setAsteriskMark(),
    new CFormField(
        (new CTextArea('message', $data['message'], ['rows' => 3]))
            ->setWidth(ZBX_TEXTAREA_STANDARD_WIDTH)
    )
]);

$form->addItem([
    new CLabel(_('Allow HTML tags'), 'allow_html'),
    new CFormField((new CCheckBox('allow_html', 1))
        ->setChecked($data['allow_html'] ?? 1)
        ->setLabel(_('Enable HTML tag translation in the message bar'))
    )
]);

$form->addItem([
    new CFormField((new CDiv(
            sprintf(_('Server time: %1$s'), date(ZBX_DATE_TIME))
        ))->addClass(ZBX_STYLE_GREY)->addStyle('padding-bottom: 2rem;')
    )
]);

$js_data['switcher'] = $switcher;
$js_data['csrf_token'] = $data['csrf_token'];
$output = [
    'header' => $data['id'] ? _('Edit message of the day') : _('New message of the day'),
    'body' => (string) (new CForm('post', '?action='.$data['submit_action']))
        ->setName('iprep_message_form')
        ->addItem($form),
    'script_inline' => 'document.dispatchEvent(new CustomEvent("iprep.form.init",'.json_encode(['detail' => $js_data]).'))',
    'buttons' => $buttons
];

if ($data['user']['debug_mode'] == GROUP_DEBUG_MODE_ENABLED) {
    CProfiler::getInstance()->stop();
    $output['debug'] = CProfiler::getInstance()->make()->toString();
}

echo json_encode($output);