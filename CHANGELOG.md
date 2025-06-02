# Changelog - Message of the Day

## Fork Monzphere (2024)

### Alterações
- Fork do projeto original da initMAX s.r.o.
- Substituição de branding initMAX por Monzphere
- Atualização de copyrights para incluir Monzphere
- Simplificação do README.md
- Remoção de referências a versão PRO

---

## Versão original initMAX

### Features
- `actions/*`
- `assets/img/*` - Novos ícones
- `assets/css/motd.css` - Remoção do logo initMAX
- `assets/js/*`
- `partials/*`
- `service/*`
- `views/*`
- `manifest.json`
- `Module.php`

# Changelog

## Version 2.1 - Implementação das Funcionalidades PRO

### ✅ Funcionalidades Implementadas

- **Número ilimitado de mensagens**: Removida a limitação que permitia apenas uma mensagem por módulo
- **Show since feature**: Implementado campo para definir quando a mensagem deve começar a aparecer
- **Repeat feature**: Sistema completo de repetição de mensagens com opções de:
  - Intervalo de repetição (diário, semanal, mensal, anual)
  - Frequência personalizada
  - Término por data ou contagem de ocorrências
- **Message bar color settings**: Configuração de cores personalizadas para:
  - Cor da barra de mensagem
  - Cor do show since
  - Cor do active since
  - Cor do active till
- **Remoção do logo initMAX**: Removido o logo da barra de mensagens e formulários
- **Suporte a HTML**: Mensagens agora suportam tags HTML com opção de habilitar/desabilitar

### 🔧 Melhorias Técnicas

- Atualizado formulário para suportar todos os novos campos
- Implementada validação adequada para campos de repetição
- Melhorada a lógica de exibição de mensagens considerando show_since
- Adicionado suporte para cores personalizadas no JavaScript
- Corrigida compatibilidade com classes do Zabbix (CSelect, CTextBox com type="color")
- Implementado seletor de cores funcional usando CTag('input') com type="color"
- Adicionado suporte condicional para renderização HTML nas mensagens

### 📦 Arquivos Modificados

- `views/motd.form.php` - Formulário atualizado com novos campos
- `service/Message.php` - Novos campos adicionados e lógica de cores
- `actions/MotdForm.php` - Valores padrão para novos campos
- `actions/MotdFormSubmit.php` - Validação e processamento dos novos campos
- `assets/css/motd.css` - Remoção do logo initMAX
- `assets/js/motd.js` - Suporte para show_since
- `service/FileStorage.php` - Lógica aprimorada para show_since
- `actions/MotdList.php` - Ordenação por novos campos
- `views/motd.list.php` - Exibição dos novos campos na lista
- `manifest.json` - Versão atualizada

### 🎯 Funcionalidades Agora Disponíveis

1. **Mensagens Múltiplas**: Criação de quantas mensagens desejar
2. **Agendamento Avançado**: Controle preciso de quando as mensagens aparecem
3. **Repetição Automática**: Sistema flexível de repetição para notificações recorrentes
4. **Personalização Visual**: Cores customizadas para diferentes estados da mensagem
5. **Interface Limpa**: Sem logos de terceiros na exibição das mensagens

### 💡 Como Usar

1. Acesse Administração → Geral → Módulos
2. Clique em "Message of the Day"
3. Crie quantas mensagens precisar com as novas opções:
   - Configure cores personalizadas
   - Defina show_since para notificações antecipadas
   - Configure repetições para mensagens recorrentes
   - Escolha as cores da barra de mensagem

## Version 2.0 - Versão Original

- Funcionalidade básica de mensagens do dia
- Uma mensagem por vez
- Campos básicos: nome, período ativo, mensagem
- Logo initMAX na interface

<!-- Structure template:---------------------------------------------------------------------------------------------------

| Version     | Date       | Type           | Description                                                                 |
|:-----------:|:----------:|:---------------|:----------------------------------------------------------------------------|
| **📦 XXX**  | 2024-08-22 | ✨ New Features | description                                                                 |
|             |            | 🔄 Updates     | description                                                                 |
|             |            | 🛠️ Bug Fixes   | description                                                                 |
|             |            | 🛡️ Security    | description                                                                 |
| **📦 1.0**  | 2024-08-22 | 🚀 Initial     | Initial release of the project                                               |

----------------------------------------------------------------------------------------------------------------------- -->