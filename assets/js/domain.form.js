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

// Código para gerenciar o formulário de domínios
(function($) {
    let data = {};

    /**
     * Inicializa o formulário de domínio
     * 
     * @param {Object} detail - Detalhes de inicialização
     */
    function initForm(detail) {
        data = detail || {};
        
        // Adicionar tokens CSRF aos botões
        if (data.csrf_token) {
            document.querySelectorAll('.overlay-dialogue-footer button').forEach(button => {
                const action = button.getAttribute('data-action');
                if (action && data.csrf_token[action]) {
                    button.setAttribute('data-csrf-token', data.csrf_token[action]);
                }
            });
        }
        
        // Validação do formulário
        const domain_input = document.querySelector('input[name="domain"]');
        if (domain_input) {
            domain_input.addEventListener('input', validateDomain);
            validateDomain.call(domain_input); // Validar inicialmente
        }
    }
    
    /**
     * Valida o campo de domínio
     */
    function validateDomain() {
        const domain = this.value.trim();
        const submit_button = document.querySelector('.overlay-dialogue-footer button[type="submit"]');
        
        // Expressão regular para validar domínios
        const domain_regex = /^(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z0-9][a-z0-9-]{0,61}[a-z0-9]$/i;
        
        if (!domain) {
            this.setCustomValidity(t('Domain cannot be empty'));
            submit_button.disabled = true;
        } else if (!domain_regex.test(domain)) {
            this.setCustomValidity(t('Please enter a valid domain name'));
            submit_button.disabled = true;
        } else {
            this.setCustomValidity('');
            submit_button.disabled = false;
        }
    }

    // Listeners de eventos
    document.addEventListener('domain.form.init', e => initForm(e.detail));
})(jQuery); 