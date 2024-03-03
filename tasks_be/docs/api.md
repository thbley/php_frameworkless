FORMAT: 1A
HOST: http://127.0.0.1:8080
VERSION: 1

# Tasks PHP API

API for managing tasks.

# Group Login

## POST /v1/customers/login

Create customer login token

+ Request (application/json)
    + Attributes
        + email (string, required)
        + password (string, required)
+ Response 201 (application/json)
    + Attributes (object)
        + token (string, required)
+ Response 400 (application/json)
    + Attributes (object)
        + error (enum[string], required)
            + `missing email`
            + `missing password`
+ Response 401 (application/json)
    + Attributes (Unauthorized)
+ Response 500 (application/json)
    + Attributes (InternalServerError)

# Group Tasks

## POST /v1/tasks

Create a task

+ Request (application/json)
    + Header
        + Authorization: Bearer {token}
    + Attributes
        + title (string, required)
        + duedate (string, required) - date, YYYY-mm-dd
+ Response 201 (application/json)
    + Attributes (Task)
+ Response 400 (application/json)
    + Attributes (object)
        + error (enum[string], required)
            + `missing title`
            + `invalid duedate`
+ Response 401 (application/json)
    + Attributes (Unauthorized)
+ Response 500 (application/json)
    + Attributes (InternalServerError)

## GET /v1/tasks{?completed}

Get current or completed tasks

+ Parameters
    + page: `1` (number, optional)
    + completed: `1` (enum[string], optional)
        + Members
            + `0`
            + `1`
+ Request (application/json)
    + Header
        Authorization: Bearer {token}
+ Response 200 (application/json)
    + Attributes (array[Task], fixed-type)
+ Response 401 (application/json)
    + Attributes (Unauthorized)
+ Response 500 (application/json)
    + Attributes (InternalServerError)

## GET /v1/tasks/{taskId}

Get single task

+ Parameters
    + taskId (number, required)
+ Request (application/json)
    + Header
        + Authorization: Bearer {token}
+ Response 200 (application/json)
    + Attributes (Task)
+ Response 401 (application/json)
    + Attributes (Unauthorized)
+ Response 404 (application/json)
    + Attributes (NotFound)
+ Response 500 (application/json)
    + Attributes (InternalServerError)

## PUT /v1/tasks/{taskId}

Updates a task

+ Parameters
    + taskId (number, required)
+ Request (application/json)
    + Header
        + Authorization: Bearer {token}
    + Attributes
        + title (string, required)
        + duedate (string, required) - date, YYYY-mm-dd
        + completed (boolean, required)
+ Response 200 (application/json)
    + Attributes (Task)
+ Response 400 (application/json)
    + Attributes (object)
        + error (enum[string], required)
            + `missing title`
            + `invalid duedate`
+ Response 401 (application/json)
    + Attributes (Unauthorized)
+ Response 404 (application/json)
    + Attributes (NotFound)
+ Response 500 (application/json)
    + Attributes (InternalServerError)

## DELETE /v1/tasks/{taskId}

Delete a task

+ Parameters
    + taskId (number, required)
+ Request (application/json)
    + Header
        + Authorization: Bearer {token}
+ Response 204
+ Response 401 (application/json)
    + Attributes (Unauthorized)
+ Response 404 (application/json)
    + Attributes (NotFound)
+ Response 500 (application/json)
    + Attributes (InternalServerError)

# Data Structures

## Task (object)
+ id (number, required)
+ title (string, required)
+ duedate: `2023-01-02` (string, required) - date, YYYY-mm-dd
+ completed (boolean, required)

## InternalServerError (object)
+ error (enum[string], required)
    + `internal server error`

## NotFound (object)
+ error (enum[string], required)
    + `task not found`

## Unauthorized (object)
+ error (enum[string], required)
    + `unauthorized`
