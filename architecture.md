PHP Frameworkless Micro Service Example
----------------------------------------

#### Design principles

    no full-stack framework (frameworkless), best performance, less complexity, more flexibility, minimum amount of code
    no learning of frameworks, no upgrading of frameworks, no dependancy on frameworks, you build it, you own it
    enable auto-complete for _everything_ in the IDE
    no ORM, repositories use SQL directly with PDO for getting all SQL features and best performance
    use PHP directly as template engine, no extra language in the language, best performance by using opcache
    use JWT-tokens instead of session handling
    use composer for auto-loading and libraries
    late initialization of objects with a static DI container (e.g. reduce duration of database connections)
    configuration parameters are stored in a single php class per environment
    use minimized alpine containers whenever possible
    SOLID, DRY, KISS, DOP (Data-Oriented Programming)

#### Code principles

    performance, security and code readability first
    prefer class[] or scalar[] over mixed arrays
    class inheritance only for tests, exceptions and verify existence of properties
    no interfaces, no traits, no attributes/annotations
    no static methods, no static variables
    no magic "__foo" functions (except __construct())
    no property defaults in models, always require initialization
    no nullable scalar properties
    no method parameter defaults
    no eval(), no shell execution like exec()
    no reflection and no variable functions in application code
    no endless for/while loops
    require typed properties, parameter types and return types (totally typed)
    superglobals only in bootstrap (index.php)
    try-catch only in bootstrap and routing
    models and configs as data objects without methods (no getters/setters, no yaml/xml)
    psalm level 1, phpstan level max
    test code coverage >99.9%
    type coverage >99.9%
    integration tests must verify that data is persisted correctly in the database
    feature tests must validate requests and responses against the given OpenAPI schema
    infrastructure tests should validate most critical configuration settings
    e2e tests should cover most important business processes
    automate all recurring development processes (application, infrastructure, security and performance testing)

#### Folder structure

    Config: credentials, connection parameters, logfiles
    Controllers: validate and process data, independant from request and response handlers
    Exceptions: custom exceptions
    Framework: authentication, DI container, logger, output, router
    Migrations: define schemas and initial data for the database
    Models: data objects
    Repositories: database queries, map database rows to models
    Routes: route http requests to controllers, serialize results, create response
    Serializer: serialize models to arrays
    Services: connect to external services, data processing
    Views: html templates and template definitions
    cli: cron jobs, helper scripts

#### Exceptions

    HttpException
        API returns _Message_ as json and _Code_ as HTTP status code,
        logs the error in the customer log
        get possible HTTP errors: grep -rh "new HttpException(" tasks_be/src/ | sort | uniq

    Exception, Throwable
        API returns "internal server error" as json and "500" as HTTP status code,
        logs the error in the system log

#### Data flow

    Bootstrap -> Http-Router -> Controller -> Repository|Service -> Serializer -> Output

    Cli -> Http-Router -> Controller -> Repository|Service -> Output

#### Timeouts, Limits

    PHP max_execution_time, FPM request_terminate_timeout: 30s
    Docker stop_grace_period: 30s
    PHP post_max_size, upload_max_filesize: 1M
    nginx client_max_body_size: 1M
    MySQL, ClickHouse, Redis connect timeout: 3s
    PHP memory_limit = 32M
    maximum login failures per hour: 10
    PHPUnit maximum duration per test: 50ms
    PHPUnit slow query log: 10ms

#### [Pipelines, CI](https://github.com/thbley/php_frameworkless/actions/workflows/build.yml) (Github Actions)

    runs on every push or on demand
    build containers
    check containers for vulnerabilities with trivy, docker scout
    show php and composer library versions
    download composer packages
    check composer.lock for outdated packages and vulnerabilities
    run phpcsfixer, psalm, psalm taint analyzer, phpstan, rector, phpmd
    run database migrations
    run phpunit tests
    download npm packages
    check package-json.lock for outdated packages and vulnerabilities
    run biome, stylelint, tsc (linting), esbuild
    run vitest, playwright, lighthouse
    collect statistics
    run pipeline locally: .github/workflows/php_local.sh

#### [Dependency updates](https://github.com/thbley/php_frameworkless/actions/workflows/dependencies.yml) (Github Actions)

    runs daily or on demand
    creates a pull request for composer and npm library updates
