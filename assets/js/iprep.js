document.addEventListener('DOMContentLoaded', () => {
    let messages = cookie.read('iprep_message');
    const bel = document.querySelector('header').nextElementSibling;

    if (messages === null || bel === null) {
        return;
    }

    messages = JSON.parse(base64_decode(messages));
    
    // Se não for um array, converter para array para manter compatibilidade
    if (!Array.isArray(messages)) {
        messages = [messages];
    }
    
    // Processar cada mensagem
    messages.forEach((message, index) => {
        // VERIFICAÇÃO ADICIONAL: Respeitar show_since no frontend
        const currentTime = Math.floor(Date.now() / 1000); // timestamp atual
        
        // Se tem show_since definido, verificar se já chegou na hora
        if (message.show_since) {
            const showSinceTime = new Date(message.show_since).getTime() / 1000;
            if (showSinceTime > currentTime) {
                // Ainda não chegou na hora de mostrar esta mensagem
                return; // Pular para a próxima mensagem
            }
        }
        
        const root = document.documentElement;
        
        // Template atualizado para incluir show_since se disponível
        let templateContent = `
            <div class="iprep-box-details">`;
        
        if (message.show_since) {
            const showSinceColor = message.show_since_color ? `style="color: #${message.show_since_color};"` : '';
            templateContent += `
                <span ${showSinceColor}>Show since:</span>
                <datetime>#{show_since}</datetime>`;
        }
        
        const activeSinceColor = message.active_since_color ? `style="color: #${message.active_since_color};"` : '';
        const activeTillColor = message.active_till_color ? `style="color: #${message.active_till_color};"` : '';
        
        templateContent += `
                <span ${activeSinceColor}>Active since:</span>
                <datetime>#{active_since}</datetime>
                <span ${activeTillColor}>Active till:</span>
                <datetime>#{active_till}</datetime>
            </div>
        `;

        const el = makeMessageBox('info', [''], null, true)[0];
        
        el.classList.add('iprep-box');
        // Adicionar classe única para cada mensagem
        el.classList.add('iprep-box-' + index);
        
        // Aplicar cor específica da mensagem
        el.style.setProperty('--iprep-color-primary', '#' + (message.color || '1f65f4'));
        
        // Criar elemento para a mensagem
        const messageDiv = document.createElement('div');
        messageDiv.style.cssText = 'padding: 8px 12px; margin-bottom: 8px;';
        
        // Definir conteúdo baseado em allow_html
        if (message.allow_html && message.allow_html == 1) {
            messageDiv.innerHTML = message.message;
        } else {
            messageDiv.textContent = message.message;
        }
        
        // Inserir antes dos detalhes
        const msgDetails = el.querySelector('.msg-details');
        msgDetails.parentNode.insertBefore(messageDiv, msgDetails);
        
        // Adicionar template dos detalhes
        msgDetails.appendChild(new Template(templateContent).evaluateToElement(message));
        
        el.querySelector('.btn-overlay-close').addEventListener('click', () => el.remove());
        
        // Adicionar um espaçamento entre as mensagens
        if (index > 0) {
            el.style.marginTop = '10px';
        }
        
        bel.parentNode.insertBefore(el, bel);
    });

    function base64_decode(t){var r,e,o,c,f,a,d="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",h="",C=0;for(t=t.replace(/[^A-Za-z0-9\+\/\=]/g,"");C<t.length;)r=d.indexOf(t.charAt(C++))<<2|(c=d.indexOf(t.charAt(C++)))>>4,e=(15&c)<<4|(f=d.indexOf(t.charAt(C++)))>>2,o=(3&f)<<6|(a=d.indexOf(t.charAt(C++))),h+=String.fromCharCode(r),64!=f&&(h+=String.fromCharCode(e)),64!=a&&(h+=String.fromCharCode(o));utftext=h;for(var n="",i=(C=0,c1=c2=0);C<utftext.length;)(i=utftext.charCodeAt(C))<128?(n+=String.fromCharCode(i),C++):i>191&&i<224?(c2=utftext.charCodeAt(C+1),n+=String.fromCharCode((31&i)<<6|63&c2),C+=2):(c2=utftext.charCodeAt(C+1),c3=utftext.charCodeAt(C+2),n+=String.fromCharCode((15&i)<<12|(63&c2)<<6|63&c3),C+=3);return n}
});
