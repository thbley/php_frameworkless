<?php

namespace TaskService\Services;

use Exception;
use TaskService\Models\Email;

class EmailService
{
    public function send(Email $email): void
    {
        if ($email->content === '') {
            throw new Exception('missing content');
        }

        $headers = [
            'From' => $email->from,
            'Content-Type' => 'text/html; charset=utf-8',
            'Content-Transfer-Encoding' => 'quoted-printable',
        ];

        $subject = '=?UTF-8?Q?' . quoted_printable_encode($email->subject) . '?=';

        if (!mail($email->recipients, $subject, quoted_printable_encode($email->content), $headers)) {
            $message = sprintf('failed to send %s to %s', $email->subject, $email->recipients);

            trigger_error($message, E_USER_WARNING);
        }
    }
}
