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

$this->addJsFile('colorpicker.js');
$this->addJsFile('class.calendar.js');

$view_url = (new CUrl('zabbix.php'))
    ->setArgument('action', 'module.iprep.domain')
    ->getUrl();

$form = (new CForm())
    ->removeId()
    ->setName('js-domain-list');

$table = (new CTableInfo())
    ->setHeader([
        (new CColHeader(
            (new CCheckBox('all_domains'))->onClick("checkAll('".$form->getName()."', 'all_domains', 'ids');")
        ))->addClass(ZBX_STYLE_CELL_WIDTH),
        make_sorting_header(_('Domain'), 'domain', $data['sort'], $data['sortorder'], $view_url),
        _('Description'),
        make_sorting_header(_('Score'), 'score', $data['sort'], $data['sortorder'], $view_url),
        make_sorting_header(_('Category'), 'category', $data['sort'], $data['sortorder'], $view_url),
        make_sorting_header(_('Last Check'), 'last_check', $data['sort'], $data['sortorder'], $view_url),
        _('Actions')
    ]);

$score_label = [
    'Alto Risco' => [ZBX_STYLE_COLOR_NEGATIVE, _('Alto Risco')],
    'Médio Risco' => [ZBX_STYLE_COLOR_WARNING, _('Médio Risco')],
    'Baixo Risco' => [ZBX_STYLE_COLOR_WARNING_ALT, _('Baixo Risco')],
    'Seguro' => [ZBX_STYLE_COLOR_POSITIVE, _('Seguro')]
];

$event = 'document.dispatchEvent(new CustomEvent("domain.form.edit", {detail:{id:"%1$s"}}))';
$check_event = 'document.dispatchEvent(new CustomEvent("domain.check", {detail:{id:"%1$s"}}))';

foreach ($data['domains'] as $domain) {
    // Determinar o estado de risco
    $score = isset($domain['status']) && isset($domain['status']['score']) ? $domain['status']['score'] : 0;
    $category = isset($domain['status']) && isset($domain['status']['category']) ? $domain['status']['category'] : 'Seguro';
    
    $score_style = $score_label[$category] ?? [ZBX_STYLE_GREY, $category];
    
    // Preparar ações
    $actions = [
        (new CLink(_('Check'), '#'))
            ->addClass(ZBX_STYLE_BTN_LINK)
            ->addClass('js-check-domain')
            ->setAttribute('data-id', $domain['id'])
            ->onClick(sprintf($check_event, $domain['id'])),
        (new CLink(_('Edit'), '#'))
            ->addClass(ZBX_STYLE_BTN_LINK)
            ->setAttribute('data-id', $domain['id'])
            ->onClick(sprintf($event, $domain['id']))
    ];
    
    // Formatar última verificação
    $last_check = isset($domain['status']) && isset($domain['status']['timestamp']) 
        ? date(ZBX_DATE_TIME, $domain['status']['timestamp']) 
        : _('Never');

    $table->addRow([
        new CCheckBox('ids['.$domain['id'].']', $domain['id']),
        (new CLink($domain['domain'], '#'))->onClick(sprintf($event, $domain['id'])),
        $domain['description'] ?? '',
        (new CSpan($score))->addClass($score >= 50 ? ZBX_STYLE_COLOR_NEGATIVE : ($score >= 20 ? ZBX_STYLE_COLOR_WARNING : ZBX_STYLE_COLOR_POSITIVE)),
        (new CDiv($score_style[1]))->addClass($score_style[0]),
        $last_check,
        (new CDiv($actions))->addClass(ZBX_STYLE_ACTION_BUTTONS)
    ]);
}


$form->addItem([$table, $data['paging']]);

// Filtro para o dashboard
$filter_form = (new CFilter())
    ->setResetUrl((new CUrl('zabbix.php'))->setArgument('action', 'module.iprep.domain'))
    ->setProfile($data['filter']['filter_profile'])
    ->setActiveTab($data['filter']['filter_tab']);

$filter_column = (new CFormGrid());

// Campo para filtrar por domínio
$filter_column->addItem([
    new CLabel(_('Domain'), 'filter_domain'),
    new CFormField(
        (new CTextBox('filter_domain', $data['filter']['domain']))
            ->setWidth(ZBX_TEXTAREA_FILTER_STANDARD_WIDTH)
    )
]);

// Campo para filtrar por score mínimo
$filter_column->addItem([
    new CLabel(_('Minimum score'), 'filter_score'),
    new CFormField(
        (new CNumericBox('filter_score', $data['filter']['score'], 3))
            ->setWidth(ZBX_TEXTAREA_NUMERIC_STANDARD_WIDTH)
    )
]);

// Campo para filtrar por categoria
$category_select = (new CSelect('filter_category'))
    ->setValue($data['filter']['category'])
    ->setFocusableElementId('filter_category')
    ->addOption(new CSelectOption('', _('Any')));

foreach ($data['categories'] as $category) {
    $category_select->addOption(new CSelectOption($category, $category));
}

$filter_column->addItem([
    new CLabel(_('Category'), 'filter_category'),
    new CFormField($category_select)
]);

$filter_form->addFilterTab(_('Filter'), [$filter_column]);

$form->addItem(
    new CActionButtonList('action', 'ids', [
        'domain.check' => [
            'name' => _('Check Selected'),
            'attributes' => [
                'class' => ZBX_STYLE_BTN_ALT,
                'formaction' => 'domain.check'
            ],
            'csrf_token' => $data['csrf_token']['domain.check']
        ],
        'domain.form.delete' => [
            'name' => _('Delete'),
            'attributes' => [
                'class' => ZBX_STYLE_BTN_ALT,
                'formaction' => 'domain.form.delete',
            ],
            'csrf_token' => $data['csrf_token']['domain.form.delete'],
            'confirm_singular' => _('Delete selected domain?'),
            'confirm_plural' => _('Delete selected domains?')
        ]
    ], 'domain')
);

(new CHtmlPage())
    ->addItem((new CDiv())->setId('domain-list-page'))
    ->setTitle(_('Monitor de BlackList de Domínios'))
    ->setControls(
        (new CTag('nav', true,
            (new CList())
                ->addItem(
                    (new CSubmitButton(_('Add domain')))
                        ->onClick('document.dispatchEvent(new CustomEvent("domain.form.edit", {detail:{}}))')
                )
        ))->setAttribute('aria-label', _('Content controls'))
    )
    ->addItem($filter_form)
    ->addItem($form)
    ->show(); 