<?php
return array (
  'openapi' => '3.0.3',
  'info' => 
  array (
    'title' => 'Tasks PHP API',
    'version' => '1',
    'description' => 'API for managing tasks.',
  ),
  'servers' => 
  array (
    0 => 
    array (
      'url' => 'http://127.0.0.1:8080',
    ),
  ),
  'paths' => 
  array (
    '/v1/customers/login' => 
    array (
      'post' => 
      array (
        'responses' => 
        array (
          201 => 
          array (
            'description' => 'Created',
            'headers' => 
            array (
            ),
            'content' => 
            array (
              'application/json' => 
              array (
                'schema' => 
                array (
                  'type' => 'object',
                  'required' => 
                  array (
                    0 => 'token',
                  ),
                  'properties' => 
                  array (
                    'token' => 
                    array (
                      'type' => 'string',
                    ),
                  ),
                ),
                'example' => 
                array (
                  'token' => '',
                ),
              ),
            ),
          ),
          400 => 
          array (
            'description' => 'Bad Request',
            'headers' => 
            array (
            ),
            'content' => 
            array (
              'application/json' => 
              array (
                'schema' => 
                array (
                  'type' => 'object',
                  'required' => 
                  array (
                    0 => 'error',
                  ),
                  'properties' => 
                  array (
                    'error' => 
                    array (
                      'type' => 'string',
                      'enum' => 
                      array (
                        0 => 'missing email',
                        1 => 'missing password',
                      ),
                    ),
                  ),
                ),
                'example' => 
                array (
                  'error' => 'missing email',
                ),
              ),
            ),
          ),
          401 => 
          array (
            'description' => 'Unauthorized',
            'headers' => 
            array (
            ),
            'content' => 
            array (
              'application/json' => 
              array (
                'schema' => 
                array (
                  '$ref' => '#/components/schemas/Unauthorized',
                ),
                'example' => 
                array (
                  'error' => 'unauthorized',
                ),
              ),
            ),
          ),
          500 => 
          array (
            'description' => 'Internal Server Error',
            'headers' => 
            array (
            ),
            'content' => 
            array (
              'application/json' => 
              array (
                'schema' => 
                array (
                  '$ref' => '#/components/schemas/InternalServerError',
                ),
                'example' => 
                array (
                  'error' => 'internal server error',
                ),
              ),
            ),
          ),
        ),
        'summary' => '',
        'operationId' => '',
        'description' => 'Create customer login token',
        'tags' => 
        array (
          0 => 'Login',
        ),
        'parameters' => 
        array (
        ),
        'requestBody' => 
        array (
          'content' => 
          array (
            'application/json' => 
            array (
              'example' => 
              array (
                'email' => '',
                'password' => '',
              ),
              'schema' => 
              array (
                'type' => 'object',
                'required' => 
                array (
                  0 => 'email',
                  1 => 'password',
                ),
                'properties' => 
                array (
                  'email' => 
                  array (
                    'type' => 'string',
                  ),
                  'password' => 
                  array (
                    'type' => 'string',
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
    ),
    '/v1/tasks' => 
    array (
      'post' => 
      array (
        'responses' => 
        array (
          201 => 
          array (
            'description' => 'Created',
            'headers' => 
            array (
            ),
            'content' => 
            array (
              'application/json' => 
              array (
                'schema' => 
                array (
                  '$ref' => '#/components/schemas/Task',
                ),
                'example' => 
                array (
                  'id' => 0,
                  'title' => '',
                  'duedate' => '2023-01-02',
                  'completed' => false,
                ),
              ),
            ),
          ),
          400 => 
          array (
            'description' => 'Bad Request',
            'headers' => 
            array (
            ),
            'content' => 
            array (
              'application/json' => 
              array (
                'schema' => 
                array (
                  'type' => 'object',
                  'required' => 
                  array (
                    0 => 'error',
                  ),
                  'properties' => 
                  array (
                    'error' => 
                    array (
                      'type' => 'string',
                      'enum' => 
                      array (
                        0 => 'missing title',
                        1 => 'invalid duedate',
                      ),
                    ),
                  ),
                ),
                'example' => 
                array (
                  'error' => 'missing title',
                ),
              ),
            ),
          ),
          401 => 
          array (
            'description' => 'Unauthorized',
            'headers' => 
            array (
            ),
            'content' => 
            array (
              'application/json' => 
              array (
                'schema' => 
                array (
                  '$ref' => '#/components/schemas/Unauthorized',
                ),
                'example' => 
                array (
                  'error' => 'unauthorized',
                ),
              ),
            ),
          ),
          500 => 
          array (
            'description' => 'Internal Server Error',
            'headers' => 
            array (
            ),
            'content' => 
            array (
              'application/json' => 
              array (
                'schema' => 
                array (
                  '$ref' => '#/components/schemas/InternalServerError',
                ),
                'example' => 
                array (
                  'error' => 'internal server error',
                ),
              ),
            ),
          ),
        ),
        'summary' => '',
        'operationId' => '',
        'description' => 'Create a task',
        'tags' => 
        array (
          0 => 'Tasks',
        ),
        'parameters' => 
        array (
          0 => 
          array (
            'name' => '+',
            'in' => 'header',
            'description' => 'e.g. Authorization: Bearer {token}',
            'required' => false,
            'schema' => 
            array (
              'type' => 'string',
            ),
            'example' => 'Authorization: Bearer {token}',
          ),
        ),
        'requestBody' => 
        array (
          'content' => 
          array (
            'application/json' => 
            array (
              'example' => 
              array (
                'title' => '',
                'duedate' => '',
              ),
              'schema' => 
              array (
                'type' => 'object',
                'required' => 
                array (
                  0 => 'title',
                  1 => 'duedate',
                ),
                'properties' => 
                array (
                  'title' => 
                  array (
                    'type' => 'string',
                  ),
                  'duedate' => 
                  array (
                    'type' => 'string',
                    'description' => 'date, YYYY-mm-dd',
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
      'get' => 
      array (
        'responses' => 
        array (
          200 => 
          array (
            'description' => 'OK',
            'headers' => 
            array (
            ),
            'content' => 
            array (
              'application/json' => 
              array (
                'schema' => 
                array (
                  'type' => 'array',
                  'items' => 
                  array (
                    '$ref' => '#/components/schemas/Task',
                  ),
                ),
                'example' => 
                array (
                  0 => 
                  array (
                    'id' => 0,
                    'title' => '',
                    'duedate' => '2023-01-02',
                    'completed' => false,
                  ),
                ),
              ),
            ),
          ),
          401 => 
          array (
            'description' => 'Unauthorized',
            'headers' => 
            array (
            ),
            'content' => 
            array (
              'application/json' => 
              array (
                'schema' => 
                array (
                  '$ref' => '#/components/schemas/Unauthorized',
                ),
                'example' => 
                array (
                  'error' => 'unauthorized',
                ),
              ),
            ),
          ),
          500 => 
          array (
            'description' => 'Internal Server Error',
            'headers' => 
            array (
            ),
            'content' => 
            array (
              'application/json' => 
              array (
                'schema' => 
                array (
                  '$ref' => '#/components/schemas/InternalServerError',
                ),
                'example' => 
                array (
                  'error' => 'internal server error',
                ),
              ),
            ),
          ),
        ),
        'summary' => '',
        'operationId' => '',
        'description' => 'Get current or completed tasks',
        'tags' => 
        array (
          0 => 'Tasks',
        ),
        'parameters' => 
        array (
          0 => 
          array (
            'name' => 'completed',
            'in' => 'query',
            'description' => '',
            'example' => '1',
            'schema' => 
            array (
              'type' => 'string',
              'enum' => 
              array (
                0 => '0',
                1 => '1',
              ),
            ),
          ),
          1 => 
          array (
            'name' => 'Authorization',
            'in' => 'header',
            'description' => 'e.g. Bearer {token}',
            'required' => false,
            'schema' => 
            array (
              'type' => 'string',
            ),
            'example' => 'Bearer {token}',
          ),
        ),
      ),
    ),
    '/v1/tasks/{taskId}' => 
    array (
      'get' => 
      array (
        'responses' => 
        array (
          200 => 
          array (
            'description' => 'OK',
            'headers' => 
            array (
            ),
            'content' => 
            array (
              'application/json' => 
              array (
                'schema' => 
                array (
                  '$ref' => '#/components/schemas/Task',
                ),
                'example' => 
                array (
                  'id' => 0,
                  'title' => '',
                  'duedate' => '2023-01-02',
                  'completed' => false,
                ),
              ),
            ),
          ),
          401 => 
          array (
            'description' => 'Unauthorized',
            'headers' => 
            array (
            ),
            'content' => 
            array (
              'application/json' => 
              array (
                'schema' => 
                array (
                  '$ref' => '#/components/schemas/Unauthorized',
                ),
                'example' => 
                array (
                  'error' => 'unauthorized',
                ),
              ),
            ),
          ),
          404 => 
          array (
            'description' => 'Not Found',
            'headers' => 
            array (
            ),
            'content' => 
            array (
              'application/json' => 
              array (
                'schema' => 
                array (
                  '$ref' => '#/components/schemas/NotFound',
                ),
                'example' => 
                array (
                  'error' => 'task not found',
                ),
              ),
            ),
          ),
          500 => 
          array (
            'description' => 'Internal Server Error',
            'headers' => 
            array (
            ),
            'content' => 
            array (
              'application/json' => 
              array (
                'schema' => 
                array (
                  '$ref' => '#/components/schemas/InternalServerError',
                ),
                'example' => 
                array (
                  'error' => 'internal server error',
                ),
              ),
            ),
          ),
        ),
        'summary' => '',
        'operationId' => '',
        'description' => 'Get single task',
        'tags' => 
        array (
          0 => 'Tasks',
        ),
        'parameters' => 
        array (
          0 => 
          array (
            'name' => 'taskId',
            'in' => 'path',
            'description' => '',
            'required' => true,
            'schema' => 
            array (
              'type' => 'number',
            ),
          ),
          1 => 
          array (
            'name' => '+',
            'in' => 'header',
            'description' => 'e.g. Authorization: Bearer {token}',
            'required' => false,
            'schema' => 
            array (
              'type' => 'string',
            ),
            'example' => 'Authorization: Bearer {token}',
          ),
        ),
      ),
      'put' => 
      array (
        'responses' => 
        array (
          200 => 
          array (
            'description' => 'OK',
            'headers' => 
            array (
            ),
            'content' => 
            array (
              'application/json' => 
              array (
                'schema' => 
                array (
                  '$ref' => '#/components/schemas/Task',
                ),
                'example' => 
                array (
                  'id' => 0,
                  'title' => '',
                  'duedate' => '2023-01-02',
                  'completed' => false,
                ),
              ),
            ),
          ),
          400 => 
          array (
            'description' => 'Bad Request',
            'headers' => 
            array (
            ),
            'content' => 
            array (
              'application/json' => 
              array (
                'schema' => 
                array (
                  'type' => 'object',
                  'required' => 
                  array (
                    0 => 'error',
                  ),
                  'properties' => 
                  array (
                    'error' => 
                    array (
                      'type' => 'string',
                      'enum' => 
                      array (
                        0 => 'missing title',
                        1 => 'invalid duedate',
                      ),
                    ),
                  ),
                ),
                'example' => 
                array (
                  'error' => 'missing title',
                ),
              ),
            ),
          ),
          401 => 
          array (
            'description' => 'Unauthorized',
            'headers' => 
            array (
            ),
            'content' => 
            array (
              'application/json' => 
              array (
                'schema' => 
                array (
                  '$ref' => '#/components/schemas/Unauthorized',
                ),
                'example' => 
                array (
                  'error' => 'unauthorized',
                ),
              ),
            ),
          ),
          404 => 
          array (
            'description' => 'Not Found',
            'headers' => 
            array (
            ),
            'content' => 
            array (
              'application/json' => 
              array (
                'schema' => 
                array (
                  '$ref' => '#/components/schemas/NotFound',
                ),
                'example' => 
                array (
                  'error' => 'task not found',
                ),
              ),
            ),
          ),
          500 => 
          array (
            'description' => 'Internal Server Error',
            'headers' => 
            array (
            ),
            'content' => 
            array (
              'application/json' => 
              array (
                'schema' => 
                array (
                  '$ref' => '#/components/schemas/InternalServerError',
                ),
                'example' => 
                array (
                  'error' => 'internal server error',
                ),
              ),
            ),
          ),
        ),
        'summary' => '',
        'operationId' => '',
        'description' => 'Updates a task',
        'tags' => 
        array (
          0 => 'Tasks',
        ),
        'parameters' => 
        array (
          0 => 
          array (
            'name' => 'taskId',
            'in' => 'path',
            'description' => '',
            'required' => true,
            'schema' => 
            array (
              'type' => 'number',
            ),
          ),
          1 => 
          array (
            'name' => '+',
            'in' => 'header',
            'description' => 'e.g. Authorization: Bearer {token}',
            'required' => false,
            'schema' => 
            array (
              'type' => 'string',
            ),
            'example' => 'Authorization: Bearer {token}',
          ),
        ),
        'requestBody' => 
        array (
          'content' => 
          array (
            'application/json' => 
            array (
              'example' => 
              array (
                'title' => '',
                'duedate' => '',
                'completed' => false,
              ),
              'schema' => 
              array (
                'type' => 'object',
                'required' => 
                array (
                  0 => 'title',
                  1 => 'duedate',
                  2 => 'completed',
                ),
                'properties' => 
                array (
                  'title' => 
                  array (
                    'type' => 'string',
                  ),
                  'duedate' => 
                  array (
                    'type' => 'string',
                    'description' => 'date, YYYY-mm-dd',
                  ),
                  'completed' => 
                  array (
                    'type' => 'boolean',
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
      'delete' => 
      array (
        'responses' => 
        array (
          204 => 
          array (
            'description' => 'No Content',
            'headers' => 
            array (
            ),
            'content' => 
            array (
            ),
          ),
          401 => 
          array (
            'description' => 'Unauthorized',
            'headers' => 
            array (
            ),
            'content' => 
            array (
              'application/json' => 
              array (
                'schema' => 
                array (
                  '$ref' => '#/components/schemas/Unauthorized',
                ),
                'example' => 
                array (
                  'error' => 'unauthorized',
                ),
              ),
            ),
          ),
          404 => 
          array (
            'description' => 'Not Found',
            'headers' => 
            array (
            ),
            'content' => 
            array (
              'application/json' => 
              array (
                'schema' => 
                array (
                  '$ref' => '#/components/schemas/NotFound',
                ),
                'example' => 
                array (
                  'error' => 'task not found',
                ),
              ),
            ),
          ),
          500 => 
          array (
            'description' => 'Internal Server Error',
            'headers' => 
            array (
            ),
            'content' => 
            array (
              'application/json' => 
              array (
                'schema' => 
                array (
                  '$ref' => '#/components/schemas/InternalServerError',
                ),
                'example' => 
                array (
                  'error' => 'internal server error',
                ),
              ),
            ),
          ),
        ),
        'summary' => '',
        'operationId' => '',
        'description' => 'Delete a task',
        'tags' => 
        array (
          0 => 'Tasks',
        ),
        'parameters' => 
        array (
          0 => 
          array (
            'name' => 'taskId',
            'in' => 'path',
            'description' => '',
            'required' => true,
            'schema' => 
            array (
              'type' => 'number',
            ),
          ),
          1 => 
          array (
            'name' => '+',
            'in' => 'header',
            'description' => 'e.g. Authorization: Bearer {token}',
            'required' => false,
            'schema' => 
            array (
              'type' => 'string',
            ),
            'example' => 'Authorization: Bearer {token}',
          ),
        ),
      ),
    ),
  ),
  'components' => 
  array (
    'schemas' => 
    array (
      'Task' => 
      array (
        'type' => 'object',
        'required' => 
        array (
          0 => 'id',
          1 => 'title',
          2 => 'duedate',
          3 => 'completed',
        ),
        'properties' => 
        array (
          'id' => 
          array (
            'type' => 'number',
          ),
          'title' => 
          array (
            'type' => 'string',
          ),
          'duedate' => 
          array (
            'type' => 'string',
            'example' => '2023-01-02',
            'description' => 'date, YYYY-mm-dd',
          ),
          'completed' => 
          array (
            'type' => 'boolean',
          ),
        ),
      ),
      'InternalServerError' => 
      array (
        'type' => 'object',
        'required' => 
        array (
          0 => 'error',
        ),
        'properties' => 
        array (
          'error' => 
          array (
            'type' => 'string',
            'enum' => 
            array (
              0 => 'internal server error',
            ),
          ),
        ),
      ),
      'NotFound' => 
      array (
        'type' => 'object',
        'required' => 
        array (
          0 => 'error',
        ),
        'properties' => 
        array (
          'error' => 
          array (
            'type' => 'string',
            'enum' => 
            array (
              0 => 'task not found',
            ),
          ),
        ),
      ),
      'Unauthorized' => 
      array (
        'type' => 'object',
        'required' => 
        array (
          0 => 'error',
        ),
        'properties' => 
        array (
          'error' => 
          array (
            'type' => 'string',
            'enum' => 
            array (
              0 => 'unauthorized',
            ),
          ),
        ),
      ),
    ),
  ),
  'tags' => 
  array (
    0 => 
    array (
      'name' => 'Login',
    ),
    1 => 
    array (
      'name' => 'Tasks',
    ),
  ),
);