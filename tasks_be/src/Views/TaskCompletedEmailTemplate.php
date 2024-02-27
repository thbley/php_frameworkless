<?php

use TaskService\Services\TemplateService;
use TaskService\Views\TaskCompletedEmail;

/**
 * @var TemplateService $this
 * @var TaskCompletedEmail $view
 */
$service = $this;
$email = $view;

?>
<!DOCTYPE html>
<html>
    <body>
        Task <b><?= $service->escape($email->task->title); ?></b> completed!
    </body>
</html>
