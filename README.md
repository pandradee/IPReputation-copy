<!-- *********************************************************************************************************************************** -->
<!-- *** HEADER ************************************************************************************************************************ -->
<!-- *********************************************************************************************************************************** -->
<div align="center">
    <h1>Message of the Day - Monzphere Fork</h1>
    <h4>
        Este é um fork do projeto original desenvolvido pela initMAX s.r.o.<br>
        Repositório original: <a href="https://git.initmax.cz/initMAX-Public/Zabbix-UI-Modules-Message-of-the-Day">https://git.initmax.cz/initMAX-Public/Zabbix-UI-Modules-Message-of-the-Day</a>
    </h4>
    <br>
    <h3>
        <span>
            Módulo Zabbix para informar usuários sobre eventos importantes
        </span>
    </h3>
</div>
<br>
<br>

<!-- *********************************************************************************************************************************** -->
<!-- *** SUPPORT *********************************************************************************************************************** -->
<!-- *********************************************************************************************************************************** -->
<br>
<br>

## 🤝 Apoie este Projeto

<div align="center">
    <h3>Gostou do projeto? Considere apoiar nosso trabalho!</h3>
    <p>
        Seu apoio nos ajuda a manter este projeto ativo e a continuar desenvolvendo novas funcionalidades.<br>
        Qualquer contribuição é muito bem-vinda e faz toda a diferença! 💙
    </p>
    <br>    
    <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;">
        <h4>💰 Faça sua doação via PIX:</h4>
        <br>
        <br>
        <p>
            <strong>Chave PIX:</strong> 57.361.246/0001-06<br>
            <img src="https://drive.google.com/uc?export=view&id=1F_V0sVmqM3Ko0nujZP9gpJDLoO9xA0LI">
            <br>
            <small>Escaneie o QR Code ou use a chave PIX acima</small>                 
        </p>
    </div>
    <br>
    <h4>🌟 Outras formas de apoiar:</h4>
    <p>
        ⭐ Dê uma estrela no GitHub<br>
        🐛 Reporte bugs ou sugestões<br>
        📢 Compartilhe com outros desenvolvedores<br>
        💻 Contribua com código
    </p>
    <br>
    <p>
        <em>Obrigado por apoiar projetos open source brasileiros! 🇧🇷</em>
    </p>
</div>
---
---

<div align="center">
    <h1>
        Message of the Day
    </h1>
    <h4>
        Informs Zabbix users about important events. Provides a centralized location for sharing critical updates, maintenance notifications, or other relevant information.
    </h4>
    <br>
    <img alt="Static Badge" src="https://img.shields.io/badge/Required%20Zabbix%20version-7.0-red">
    <img alt="Static Badge" src="https://img.shields.io/badge/Required%20php%20version-8.0-blue">
    <h3>
        <a href="#description">Description</a> •
        <a href="#key_features">Key features</a> •
        <a href="#installation">Installation</a>
    </h3>
</div>
<br>

<!-- *********************************************************************************************************************************** -->
<!-- *** BODY ************************************************************************************************************************** -->
<!-- *********************************************************************************************************************************** -->
<div align="center">
    <img src="./.readme/screen/dashboard.png" width="1000">
</div>
<br>
<br>

## Description
This Zabbix module informs users and administrators about important events. It provides a centralized location for sharing critical updates, maintenance notifications, or other relevant information to all system users.

<!-- *********************************************************************************************************************************** -->
<br>

## Key features
Enhance communication with Zabbix users and administrators with customizable notifications. Adjust message appearance using color coding to effectively prioritize information. The three-tiered timing system ("Show since", "Active since", "Active till") offers precise control over message visibility and relevance.

The repeat function automates recurring notifications, ideal for scheduled maintenance or periodic updates. This feature streamlines communication processes and ensures consistent information delivery.

This tool enable administrators to deliver clear, timely, and professional communications, improving operational awareness for all users.

<!-- *********************************************************************************************************************************** -->
<br>

### Message bar
Displays important notifications at the top of all application pages, including the message content with 'Active since' and 'Active till' dates. While closable, it reappears on page reload, ensuring critical information is not missed. This persistent behavior prevents users from claiming unawareness of important events.

![image](https://github.com/user-attachments/assets/b2a019dd-8a0d-4195-b964-1e9f6aef9fdc)


<!-- *********************************************************************************************************************************** -->
<br>

### Administration screen
The administration interface, accessible to users with Super Admin rights, provides a dedicated platform for managing message events within the system. It offers a clear overview of all configured notifications.

Users can efficiently organize and locate messages using filters for name, state (Any, Active, Approaching, Expired), and status (All, Enabled, Disabled). This functionality allows for quick access to relevant messages, including the ability to view and manage disabled notifications directly from the status column.

![image](https://github.com/user-attachments/assets/6a19933d-2f8a-477c-800c-3f7b4ceb2d4a)


<!-- *********************************************************************************************************************************** -->
<br>

### Repeat messages

The repeat option enables scheduling of regular, repeating messages. When activated, it offers flexible configuration:

Set the frequency of repetition, choosing from daily, weekly, monthly, or yearly intervals. Fine-tune by specifying the number of days, weeks, months, or years between occurrences.

Define the duration of the recurring message series. Choose to repeat indefinitely, set a specific end date, or limit to a certain number of occurrences.

![image](https://github.com/user-attachments/assets/d4e91cc6-66d0-419a-9740-f1b2e3821f0f)

<!-- *********************************************************************************************************************************** -->
<br>

### HTML tags support
(Upcoming feature)

In an upcoming version, we plan to introduce support for HTML tags in messages. This feature will allow:

Insertion of HTML tags into messages for enhanced formatting.
Ability to include clickable links directly in the message bar.

This functionality will enable better text formatting and more interactive message content, improving the overall user experience. Stay tuned for this enhancement in a future release.

**Example:**
  - `Upgrade Zabbix environment to 7.0.0 (<a href="https://www.zabbix.com/rn/rn7.0.0">Zabbix Release Notes</a>)`
    > Upgrade Zabbix environment to 7.0.0 (<a href="https://www.zabbix.com/rn/rn7.0.0">Zabbix Release Notes</a>)

<!-- *********************************************************************************************************************************** -->
<br>

### Configuration popup

<div align="center">
    <img src="./.readme/screen/configuration_popup.png" width="500">
</div>

<div align="center">

| Required | Field               | Description                                                                                                               |
|:--------:|---------------------|---------------------------------------------------------------------------------------------------------------------------|
| ❗        | **Name**            | The name of the event.                                                                                                    |
| ❗        | **Show since**      | Used to inform users in advance that an event will occur. Specifies the date and time from which the message is displayed to users. Additionally, it is possible to define the color of the message bar until the event becomes active.  |
| ❗        | **Active since**    | Specifies the point in time from which the event is active. During this period, it is possible to define the color of the message bar to indicate the event's status. |
| ❗        | **Active till**     | The point in time when the event is deactivated.                                                                           |
|            | **Repeat**          | This option allows you to set recurrence for displaying regular messages.                                                  |
| ❗        | **Message**         | The message for users that will be displayed in the message bar.                                                           |
| ❗        | **Allow HTML tags** | Enable HTML tag translation in the message bar. (IN DEVELOPMENT)                                                                            |

</div>

<!-- *********************************************************************************************************************************** -->
<br>
<br>

## Installation
- Connect to your Zabbix frontend server (perform on all frontend nodes) via SSH.

- Navigate to the `ui/modules/` directory (`ui` is typically located at `/usr/share/zabbix/`)
    ```sh
    cd /usr/share/zabbix/modules/
    ```

- Clone repository on your server <!-- !!! repository !!! -->
    ```sh
    git clone https://github.com/Monzphere/Zabbix-Message-Day.git
    ``` 

- Change the ownership of the directory to the user under which your Zabbix frontend is running using the `chown` command, some examples:
  - default OS users
    ```sh
    chown nginx:nginx ./Zabbix-Message-Day*
    ``` 
    ```sh
    chown apache:apache ./Zabbix-Message-Day*
    ``` 
    ```sh
    chown www-data:www-data ./Zabbix-Message-Day*
    ``` 

- Go to Zabbix frontend menu -> Administration -> General -> Modules
- Use the Scan directory button on the top
- Enable the newly discovered module
- Done
- Use it and enjoy!
<!-- *********************************************************************************************************************************** -->
<!-- *** FOOTER ************************************************************************************************************************ -->
<!-- *********************************************************************************************************************************** -->
<br>
<br>

---
---
<div align="center">
    <h4>
        Fork mantido por Monzphere<br>
        Baseado no trabalho original da initMAX s.r.o.<br>
        <br>
    </h4>
</div>
