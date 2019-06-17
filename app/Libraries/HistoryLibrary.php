<?php

namespace App\Libraries;
use App\History;

class HistoryLibrary
{
    /**
     * Create History
     *
     */
	public static function createLog($params)
	{
        $loggable_type = $params['loggable_type'];
        $loggable_id = $params['loggable_id'];
        $action = $params['action'];
        $value = $params['value'];

        $history = new History();
        $history->loggable_type = $loggable_type;
        $history->loggable_id = $loggable_id;
        $history->action = $action;
        $history->value = $value;
        $history->save();
		return $history;
	}
}
