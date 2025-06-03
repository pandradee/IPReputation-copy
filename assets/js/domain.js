/**
 * Copyright (C) 2025 Monzphere - Fork mantido por Monzphere
 *
 * This program is free software: you can redistribute it and/or modify it under the terms of
 * the GNU Affero General Public License as published by the Free Software Foundation, version 3.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License along with this program.
 * If not, see <https://www.gnu.org/licenses/>.
 */

// Código para gerenciar a interface de monitoramento de domínios
(function($) {
    let overlay = null;

    /**
     * Abre um overlay com o formulário de domínio
     * 
     * @param {Object} detail - Detalhes do evento
     */
    function openDomainForm(detail) {
        const id = detail?.id || '';
        const curl = new Curl('zabbix.php');
        curl.setArgument('action', 'domain.form.edit');
        
        if (id) {
            curl.setArgument('id', id);
        }
        
        overlay = overlays_stack.getById('domain_form') || PopUp('popup.generic', {
            dialogueid: 'domain_form',
            url: curl.getUrl(),
            buttons: [{
                'title': id ? t('Update') : t('Add'),
                'class': '',
                'keepOpen': true,
                'isSubmit': true,
                'action': function(overlay) {
                    document.dispatchEvent(new CustomEvent('domain.form.submit', {detail: {button: this, overlay}}));
                }
            }, {
                'title': t('Cancel'),
                'class': 'btn-alt',
                'cancel': true,
                'action': function() {}
            }]
        }, 'domain_form');
    }

    /**
     * Salva o formulário de domínio
     * 
     * @param {Object} detail - Detalhes do evento
     */
    function submitDomainForm(detail) {
        const $form = jQuery(detail.overlay.$dialogue.$body).find('form');
        const csrf_token = detail.button.getAttribute('data-csrf-token');
        const curl = new Curl('zabbix.php');
        curl.setArgument('action', 'domain.form.submit');
        
        // Preparar dados do formulário
        const form_data = {};
        $form.serializeArray().forEach(item => {
            form_data[item.name] = item.value;
        });
        
        // Processar tags
        if (form_data.tags) {
            const tags = form_data.tags.split(',').map(tag => tag.trim()).filter(Boolean);
            form_data.tags = tags;
        } else {
            form_data.tags = [];
        }
        
        // Adicionar token CSRF
        if (csrf_token) {
            form_data.csrf_token = csrf_token;
        }
        
        jQuery.ajax({
            url: curl.getUrl(),
            method: 'POST',
            data: JSON.stringify(form_data),
            contentType: 'application/json',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    overlayDialogueDestroy(detail.overlay.dialogueid);
                    // Recarregar a página para ver as mudanças
                    location.reload();
                } else {
                    // Exibir mensagem de erro
                    alert(response.error || t('Error saving domain'));
                }
            },
            error: function() {
                alert(t('Error connecting to server'));
            }
        });
    }

    /**
     * Verifica um domínio nas blacklists
     * 
     * @param {Object} detail - Detalhes do evento
     */
    function checkDomain(detail) {
        const id = detail?.id || '';
        if (!id) {
            return;
        }
        
        const curl = new Curl('zabbix.php');
        curl.setArgument('action', 'domain.check');
        
        // Mostrar mensagem de carregamento
        const button = event.target;
        const original_text = button.textContent;
        button.textContent = t('Checking...');
        button.disabled = true;
        
        jQuery.ajax({
            url: curl.getUrl(),
            method: 'POST',
            data: JSON.stringify({id: id}),
            contentType: 'application/json',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Recarregar a página para ver os resultados atualizados
                    location.reload();
                } else {
                    // Exibir mensagem de erro
                    alert(response.error || t('Error checking domain'));
                    button.textContent = original_text;
                    button.disabled = false;
                }
            },
            error: function() {
                alert(t('Error connecting to server'));
                button.textContent = original_text;
                button.disabled = false;
            }
        });
    }

    /**
     * Inicializa os listeners de eventos
     */
    function initEventListeners() {
        document.addEventListener('domain.form.edit', e => openDomainForm(e.detail));
        document.addEventListener('domain.form.submit', e => submitDomainForm(e.detail));
        document.addEventListener('domain.check', e => checkDomain(e.detail));
        
        // Adicionar listeners para os botões de verificação de domínio
        document.querySelectorAll('.js-check-domain').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const id = this.getAttribute('data-id');
                if (id) {
                    document.dispatchEvent(new CustomEvent('domain.check', {detail: {id}}));
                }
            });
        });
    }

    // Inicialização quando o DOM estiver pronto
    jQuery(document).ready(function() {
        initEventListeners();
    });
})(jQuery); 