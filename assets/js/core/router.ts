import $ from 'jquery';
import queryString from 'query-string';

export interface Params {
    [name: string]: string|string[];
}

import { httpclient } from 'typescript-http-client';
import HttpClient = httpclient.HttpClient;
import Request = httpclient.Request;
import newHttpClient = httpclient.newHttpClient;

interface Abortable { abort(): void; }
export type AbortablePromise<T> = Promise<T> & Abortable;


interface Route {
    name: string;
    method: string;
    pattern: string;
    prefixId: string;
}

interface RouteGroup {
    [namePrefix: string]: Route;
}

interface Routes {
    [name: string]: RouteGroup;
}

export class Router {

    private queryParams;

    private http: HttpClient;

    constructor(private routes: Routes, private routeParameters: Params, private action: string) {
        this.queryParams = Router.parseQueryParameters();
        this.http = newHttpClient();
    }

    getHttpClient(): HttpClient {
        return this.http;
    }

    init() {

        $('[data-ajax-action]').on('click',(event) => {
            let $btn = $(event.target),
                action = $btn.data('ajax-action'),
                spinner = Boolean($btn.data('ajax-spinner'));

            if (spinner) {
                $btn.append('<span class="fa fa-sync-alt fa-spin"></span>');
            }

            this.sendRequest(action).finally(() => {
                if (spinner) {
                    $btn.children('.fa-spin').remove();
                }
            });
        });
    }

    sendRequest<R = any>(name: string , parameters: Params = {}): AbortablePromise<R> {
        const route = this.getRoute(name);
        const request = this.createRequest(route, parameters);

        const body$ = this.http.executeForResponse(request).then(response => {

            const {body}: {body: any} = response;

            if (body.status === 'success') {
                return body.data;
            }

            throw new body.message;
        }) as AbortablePromise<R>;

        body$.abort = function () {
            request.abort();
        }

        return body$;
    }

    generateUrl(name: string, params: Params = {}): string {
        const route = this.getRoute(name);
        let url = this.prepareUrl(route, params);

        if (Object.keys(params).length > 0) {
            url += '?' + queryString.stringify(params, { arrayFormat: 'bracket' });
        }

        return url;
    }

    protected prepareUrl(route: Route, params: Params): string {
        return route.pattern.replace(/{(_?[A-Za-z]+)}/g, (match, name) => {
            let result;

            if (params.hasOwnProperty(name)) {
                result = params[name] as string;
                delete params[name];
            } else {
                result = match;
            }

            return result;
        });
    }

    protected createRequest(route: Route, params: Params): Request {

        if (route.prefixId === this.routeParameters._prefixId) {
            if (route.name === this.action) {
                params = {...this.routeParameters, ...this.queryParams, ...params};
            }
             else {
                params = {...this.routeParameters, ...params};
            }
        }

        let url = this.prepareUrl(route, params);

        for (const key of Object.keys(params)) {
            if (key[0] === '_') {
                delete params[key];
            }
        }

        const {method} = route;

        const options: any = {
            method,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        };

        if (method === 'GET') {
            if (Object.keys(params).length > 0) {
                url += '?' + queryString.stringify(params, { arrayFormat: 'bracket' });
            }
        }
        else {
            options.body = queryString.stringify(params, { arrayFormat: 'bracket' });
            options.contentType = 'application/x-www-form-urlencoded';
        }

        return new Request(url, options);
    }

    protected getRoute(name: string): Route {

        let prefixId = this.routeParameters._prefixId as string;

        if (!this.routes.hasOwnProperty(name)) {
            throw new Error(`cannot find route with name: ${name}`);
        }

        const routeGroup = this.routes[name];

        if (!routeGroup.hasOwnProperty(prefixId)) {
            prefixId = Object.keys(routeGroup)[0];
        }

        return routeGroup[prefixId];
    }

    static parseQueryParameters(): Params {
        return queryString.parse(location.search) as Params;
    }
}
