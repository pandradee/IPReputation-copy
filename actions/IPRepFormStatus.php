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

class IPRepFormStatus extends BaseAction
{
    public function init()
    {
        $this->setPostContentType(self::POST_CONTENT_TYPE_JSON);
    }

    protected function checkInput()
    {
        $valid = $this->validateInput([
            'id'    => 'id',
            'ids'   => 'array_id'
        ]);

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

    public function doAction()
    {
        $output = [];
        $status = $this->getAction() === 'iprep.form.enable' ? IPData::STATUS_ENABLED : IPData::STATUS_DISABLED;
        $ids = $this->getInput('ids', []);

        if ($this->hasInput('id')) {
            $ids[] = $this->getInput('id');
        }

        $messages = $this->module->storage->get(['ids' => $ids]);

        foreach ($messages as &$message) {
            $message['status'] = $status;
        }
        unset($message);

        try {
            $this->module->storage->update($messages);
            $this->module->storage->commit();

            $output['success']['title'] = $status == IPData::STATUS_ENABLED
                ? _('Messages enabled')
                : _('Messages disabled');
            $output['success']['messages'] = array_column(get_and_clear_messages(), 'message');
        }
        catch (Exception $e) {
            $output['error'] = [
                'title' => $status == IPData::STATUS_ENABLED
                    ? _('Cannot enable messages')
                    : _('Cannot disable messages'),
                'messages' => [$e->getMessage()]
            ];
        }

        $this->setResponse(new CControllerResponseData(['main_block' => json_encode($output)]));
    }
}
