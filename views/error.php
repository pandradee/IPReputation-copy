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

// Página de erro
(new CHtmlPage())
    ->addItem((new CDiv())->setId('error-page'))
    ->setTitle(_('Erro - Monitor de BlackList de Domínios'))
    ->addItem(
        (new CDiv())
            ->addClass('msg-bad')
            ->addItem([
                new CSpan(_('Erro')),
                new CBR(),
                new CSpan($data['message'] ?? _('Ocorreu um erro ao processar a solicitação.')),
                new CBR(),
                new CBR(),
                (new CButton('back'))
                    ->setAttribute('onclick', 'window.history.back();')
                    ->addClass(ZBX_STYLE_BTN_ALT)
                    ->addItem(_('Voltar'))
            ])
    )
    ->show(); 