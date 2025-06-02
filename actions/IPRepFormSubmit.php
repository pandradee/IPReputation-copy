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


namespace Modules\IPReputation\Actions;

use Exception;
use CControllerResponseData;
use Modules\IPReputation\Service\IPData;

class IPRepFormSubmit extends BaseAction
{
    public function init()
    {
        $this->setPostContentType(self::POST_CONTENT_TYPE_JSON);
    }

    protected function checkInput()
    {
        $this->module->storage->begin();
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Verificar se repeat está habilitado para ajustar validação
        $repeat_enabled = isset($input['repeat']) && $input['repeat'] == IPData::REPEAT_ENABLED;
        
        $rules = [
            'id'                    => 'id',
            'status'                => 'in '.implode(',', [IPData::STATUS_DISABLED, IPData::STATUS_ENABLED]),
            'name'                  => 'required|not_empty|string',
            'show_since'            => 'abs_time',
            'show_since_color'      => 'string',
            'active_since'          => 'required|abs_time',
            'active_since_color'    => 'string',
            'active_till'           => 'required|abs_time',
            'active_till_color'     => 'string',
            'repeat'                => 'in '.implode(',', [IPData::REPEAT_DISABLED, IPData::REPEAT_ENABLED]),
            'message'               => 'required|not_empty|string',
            'message_color'         => 'string',
            'allow_html'            => 'in 0,1'
        ];
        
        // Adicionar regras de repeat apenas se repeat estiver habilitado
        if ($repeat_enabled) {
            $rules['repeat_interval'] = 'in '.implode(',', [IPData::REPEAT_DAY, IPData::REPEAT_WEEK, IPData::REPEAT_MONTH, IPData::REPEAT_YEAR]);
            $rules['repeat_frequency'] = 'int32';
            $rules['repeat_end_type'] = 'in '.implode(',', [IPData::REPEAT_END_NEVER, IPData::REPEAT_END_DATE, IPData::REPEAT_END_COUNT]);
            $rules['repeat_end_date'] = 'abs_time';
            $rules['repeat_end_count'] = 'int32';
        }

        $valid = $this->validateInput($rules) && $this->validateTime();

        if (!$valid) {
            $this->setResponse(
                (new CControllerResponseData(['main_block' => json_encode([
                    'error' => [
                        'messages' => array_column(get_and_clear_messages(), 'message')
                    ]
                ])]))->disableView()
            );
        }

        return $valid;
    }

    protected function validateTime(): bool
    {
        $valid = true;
        $show_since = $this->hasInput('show_since') ? strtotime($this->getInput('show_since')) : 0;
        $active_since = strtotime($this->getInput('active_since'));
        $active_till = strtotime($this->getInput('active_till'));

        if ($active_since >= $active_till || $show_since > $active_since) {
            $valid = false;
            error(_('Incorrect date and time for "Show since", "Active since" or "Active till" is defined.'));
        }

        if ($this->getInput('repeat_end_type', IPData::REPEAT_END_NEVER) == IPData::REPEAT_END_DATE) {
            $repeat_end_date = strtotime($this->getInput('repeat_end_date'));
            $active_till_ymd = strtotime(date('Y-m-d', $active_till));

            if ($repeat_end_date < $active_till_ymd) {
                $valid = false;
                error(_('Incorrect date for "Ends" is defined.'));
            }
        }

        $status = $this->getInput('status', IPData::STATUS_DISABLED);

        if ($status === IPData::STATUS_ENABLED && $valid && $active_since < $active_till) {
            $messages = $this->module->storage->get([
                'status' => IPData::STATUS_ENABLED,
                'time_from' => $show_since > 0 ? $show_since : $active_since,
                'time_till' => $active_till
            ]);
            $messages = array_column($messages, null, 'id');
            unset($messages[$this->getInput('id', -1)]);

            if ($messages) {
                $valid = false;
                error(_s('Iterval intersects with already defined for: %1$s',
                    implode(', ', array_column($messages, 'name')))
                );
            }
        }

        return $valid;
    }

    public function doAction()
    {
        $output = [];
        $message = [
            'status' => IPData::STATUS_DISABLED,
        ];

        $this->getInputs($message, IPData::FIELDS);

        // Verificar se repeat está desabilitado ANTES de processar outros campos
        $repeat_enabled = $this->getInput('repeat', IPData::REPEAT_DISABLED) == IPData::REPEAT_ENABLED;
        
        if (!$repeat_enabled) {
            // Remover campos de repeat do message e definir valores padrão
            unset($message['repeat_interval'], $message['repeat_frequency'], $message['repeat_end_type'], $message['repeat_end_date'], $message['repeat_end_count']);
            $message['repeat'] = IPData::REPEAT_DISABLED;
        }

        // Limpar campos de data de fim baseado no tipo
        if (!$repeat_enabled || $this->getInput('repeat_end_type', IPData::REPEAT_END_NEVER) != IPData::REPEAT_END_DATE) {
            unset($message['repeat_end_date']);
        }

        if (!$repeat_enabled || $this->getInput('repeat_end_type', IPData::REPEAT_END_NEVER) != IPData::REPEAT_END_COUNT) {
            unset($message['repeat_end_count']);
        }

        // Processar campos de tempo
        foreach (['show_since', 'active_since', 'active_till', 'repeat_end_date'] as $time_field) {
            if ($this->hasInput($time_field) && !empty($message[$time_field]) && isset($message[$time_field])) {
                $message[$time_field] = strtotime($message[$time_field]);
            } else if ($time_field === 'show_since') {
                $message[$time_field] = null;
            }
        }

        // Processar campos inteiros apenas se repeat estiver habilitado
        if ($repeat_enabled) {
            foreach (['repeat_interval', 'repeat_frequency', 'repeat_end_type', 'repeat_end_count'] as $int_field) {
                if ($this->hasInput($int_field) && isset($message[$int_field])) {
                    $message[$int_field] = intval($message[$int_field]);
                }
            }
        }
        
        // Sempre processar o campo repeat
        if ($this->hasInput('repeat')) {
            $message['repeat'] = intval($message['repeat']);
        }

        // Processar allow_html como checkbox
        $message['allow_html'] = $this->hasInput('allow_html') ? 1 : 0;

        try {
            if ($this->hasInput('id')) {
                $this->module->storage->update([$message]);
                $this->module->storage->commit();
                $output['success']['title'] = _('IPData updated');
            }
            else {
                $this->module->storage->create([$message]);
                $this->module->storage->commit();
                $output['success']['title'] = _('IPData created');
            }

            $output['success']['messages'] = array_column(get_and_clear_messages(), 'message');
        }
        catch (Exception $e) {
            $output['error'] = [
                'title' => $this->hasInput('id')
                    ? _('Cannot update message')
                    : _('Cannot create message'),
                'messages' => [$e->getMessage()]
            ];
        }

        $this->setResponse(new CControllerResponseData(['main_block' => json_encode($output)]));
    }
}
