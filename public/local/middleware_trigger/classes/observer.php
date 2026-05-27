<?php
defined('MOODLE_INTERNAL') || die();

class local_middleware_trigger_observer {

    public static function course_completed(\core\event\base $event) {
        global $CFG;

        error_log(">>>>>>>> GATILHO DO MOODLE ACORDOU COM SUCESSO! <<<<<<<<");

        if (empty($CFG->middleware_url) || empty($CFG->middleware_token)) {
            error_log("[Middleware Trigger] Erro: Variaveis nao configuradas.");
            return;
        }

        $eventdata = $event->get_data();

        $userid   = isset($eventdata['userid'])   ? $eventdata['userid']   : 0;
        $courseid = isset($eventdata['courseid']) ? $eventdata['courseid'] : 0;

        $data    = ['userid' => (int)$userid, 'courseid' => (int)$courseid];
        $payload = json_encode($data);

        $ch = curl_init($CFG->middleware_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($payload),
            'Authorization: Bearer ' . $CFG->middleware_token,
        ]);

        $response  = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code !== 200) {
            error_log("[Middleware Trigger Error] HTTP: " . $http_code . " Resposta: " . $response);
        } else {
            error_log("[Middleware Trigger Success] Evento enviado com sucesso para o Node!");
        }
    }
}
