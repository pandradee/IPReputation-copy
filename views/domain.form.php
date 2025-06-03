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


/**
 * @var CView $this
 * @var array $data
 */

$form = (new CFormGrid())->addClass('domain-config-form');
$action = 'document.dispatchEvent(new CustomEvent("%1$s", {detail:{button: this, overlay: arguments[0]}}))';
$js_data = [];

if ($data['form']['id']) {
    $form->addItem(new CVar('id', $data['form']['id']));

    $buttons = [
        [
            'title' => _('Update'),
            'class' => '',
            'keepOpen' => true,
            'isSubmit' => true,
            'action' => sprintf($action, 'domain.form.submit'),
            'mvc_action' => 'domain.form.submit'
        ],
        [
            'title' => _('Delete'),
            'confirmation' => _('Delete selected domain?'),
            'class' => 'btn-alt',
            'keepOpen' => true,
            'isSubmit' => false,
            'action' => sprintf($action, 'domain.form.delete'),
            'mvc_action' => 'domain.form.delete'
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
            'action' => sprintf($action, 'domain.form.submit'),
            'mvc_action' => 'domain.form.submit'
        ]
    ];
}

$form->addItem([
    new CSpan(''),
    (new CImg($this->getAssetsPath() . '/img/monzphere.png'))
        ->addClass('monzphere-logo'),
]);

$form->addItem([
    (new CLabel(_('Domain'), 'domain'))->setAsteriskMark(),
    new CFormField((new CTextBox('domain', $data['form']['domain']))
        ->setWidth(ZBX_TEXTAREA_STANDARD_WIDTH)
        ->setAriaRequired()
        ->setAttribute('autofocus', 'autofocus')
        ->setAttribute('placeholder', 'example.com')
    )
]);

$form->addItem([
    new CLabel(_('Description'), 'description'),
    new CFormField(
        (new CTextArea('description', $data['form']['description'], ['rows' => 2]))
            ->setWidth(ZBX_TEXTAREA_STANDARD_WIDTH)
            ->setAttribute('placeholder', _('Optional description for this domain'))
    )
]);

$form->addItem([
    new CLabel(_('Owner'), 'owner'),
    new CFormField((new CTextBox('owner', $data['form']['owner']))
        ->setWidth(ZBX_TEXTAREA_STANDARD_WIDTH)
        ->setAttribute('placeholder', _('Owner or department responsible for this domain'))
    )
]);

// Adicionar informações de tags (implementação simplificada)
$form->addItem([
    new CLabel(_('Tags'), 'tags'),
    new CFormField(
        (new CTextBox('tags', implode(', ', $data['form']['tags'])))
            ->setWidth(ZBX_TEXTAREA_STANDARD_WIDTH)
            ->setAttribute('placeholder', _('Comma separated tags, e.g.: prod, external, crm'))
    )
]);

// Adicionar informações do formulário para o JavaScript
$js_data['csrf_token'] = $data['csrf_token'];

$output = [
    'header' => $data['title'],
    'body' => (string) (new CForm('post', '?action=domain.form.submit'))
        ->setName('domain_form')
        ->addItem($form),
    'script_inline' => 'document.dispatchEvent(new CustomEvent("domain.form.init",'.json_encode(['detail' => $js_data]).'))',
    'buttons' => $data['buttons']
];

if ($data['user']['debug_mode'] == GROUP_DEBUG_MODE_ENABLED) {
    CProfiler::getInstance()->stop();
    $output['debug'] = CProfiler::getInstance()->make()->toString();
}

echo json_encode($output); 