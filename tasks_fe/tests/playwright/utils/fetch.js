// @ts-check

/**
 * @param {import("@playwright/test").Route} route
 */
export const emulateApiFetch = (route) => {
    const request = `${route.request().method()} ${route.request().url()}`;
    let content = '';
    let status = 200;

    switch (request) {
        case 'GET https://nginx/v1/tasks?page=1&completed=0': {
            content = '[{"id":12345,"title":"Test Title 123","duedate":"2024-02-01","completed":false}]';
            break;
        }
        case 'GET https://nginx/v1/tasks?page=2&completed=0': {
            content = '[{"id":12347,"title":"Test Title 456","duedate":"2024-03-01","completed":false}]';
            break;
        }
        case 'GET https://nginx/v1/tasks?page=1&completed=1': {
            content = '[{"id":54321,"title":"Test Title completed","duedate":"2024-03-01","completed":true}]';
            break;
        }
        case 'POST https://nginx/v1/customers/login': {
            const email = route.request().postDataJSON().email;
            if (email === 'invalid@invalid.local') {
                content = `{"error": "unauthorized"}`;
                status = 401;
            } else {
                content = `{"token": "Bearer .${btoa(JSON.stringify({ email: 'foo@bar.baz' }))}."}`;
                status = 201;
            }
            break;
        }
        case 'DELETE https://nginx/v1/tasks/12345': {
            status = 204;
            break;
        }
        default: {
            console.error(`Undefined route ${request}`);
            status = 501;
        }
    }

    route.fulfill({ status: status, body: content, contentType: 'application/json' });
};
