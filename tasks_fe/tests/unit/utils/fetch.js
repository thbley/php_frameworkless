// @ts-check

export function fetch500() {
    return new Promise((resolve) => {
        const headers = new Headers();
        const response = new Response(null, { status: 500, statusText: 'internal server error', headers: headers });
        resolve(response);
    });
}

export function fetchReject() {
    return new Promise((_resolve, reject) => {
        reject(new Error('rejected'));
    });
}
