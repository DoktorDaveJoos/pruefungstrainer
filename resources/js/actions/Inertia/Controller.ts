import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/agb'
*/
const Controllere0ac0b42e67455fe4ca34384c73b6f22 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controllere0ac0b42e67455fe4ca34384c73b6f22.url(options),
    method: 'get',
})

Controllere0ac0b42e67455fe4ca34384c73b6f22.definition = {
    methods: ["get","head"],
    url: '/agb',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/agb'
*/
Controllere0ac0b42e67455fe4ca34384c73b6f22.url = (options?: RouteQueryOptions) => {
    return Controllere0ac0b42e67455fe4ca34384c73b6f22.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/agb'
*/
Controllere0ac0b42e67455fe4ca34384c73b6f22.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controllere0ac0b42e67455fe4ca34384c73b6f22.url(options),
    method: 'get',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/agb'
*/
Controllere0ac0b42e67455fe4ca34384c73b6f22.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controllere0ac0b42e67455fe4ca34384c73b6f22.url(options),
    method: 'head',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/agb'
*/
const Controllere0ac0b42e67455fe4ca34384c73b6f22Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: Controllere0ac0b42e67455fe4ca34384c73b6f22.url(options),
    method: 'get',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/agb'
*/
Controllere0ac0b42e67455fe4ca34384c73b6f22Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: Controllere0ac0b42e67455fe4ca34384c73b6f22.url(options),
    method: 'get',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/agb'
*/
Controllere0ac0b42e67455fe4ca34384c73b6f22Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: Controllere0ac0b42e67455fe4ca34384c73b6f22.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

Controllere0ac0b42e67455fe4ca34384c73b6f22.form = Controllere0ac0b42e67455fe4ca34384c73b6f22Form
/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/datenschutz'
*/
const Controllerbdac400a0ce7e1d93c22d58bb3768ce2 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controllerbdac400a0ce7e1d93c22d58bb3768ce2.url(options),
    method: 'get',
})

Controllerbdac400a0ce7e1d93c22d58bb3768ce2.definition = {
    methods: ["get","head"],
    url: '/datenschutz',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/datenschutz'
*/
Controllerbdac400a0ce7e1d93c22d58bb3768ce2.url = (options?: RouteQueryOptions) => {
    return Controllerbdac400a0ce7e1d93c22d58bb3768ce2.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/datenschutz'
*/
Controllerbdac400a0ce7e1d93c22d58bb3768ce2.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controllerbdac400a0ce7e1d93c22d58bb3768ce2.url(options),
    method: 'get',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/datenschutz'
*/
Controllerbdac400a0ce7e1d93c22d58bb3768ce2.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controllerbdac400a0ce7e1d93c22d58bb3768ce2.url(options),
    method: 'head',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/datenschutz'
*/
const Controllerbdac400a0ce7e1d93c22d58bb3768ce2Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: Controllerbdac400a0ce7e1d93c22d58bb3768ce2.url(options),
    method: 'get',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/datenschutz'
*/
Controllerbdac400a0ce7e1d93c22d58bb3768ce2Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: Controllerbdac400a0ce7e1d93c22d58bb3768ce2.url(options),
    method: 'get',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/datenschutz'
*/
Controllerbdac400a0ce7e1d93c22d58bb3768ce2Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: Controllerbdac400a0ce7e1d93c22d58bb3768ce2.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

Controllerbdac400a0ce7e1d93c22d58bb3768ce2.form = Controllerbdac400a0ce7e1d93c22d58bb3768ce2Form
/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/impressum'
*/
const Controllercff9dfc7b584addc1377d815f55e12d4 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controllercff9dfc7b584addc1377d815f55e12d4.url(options),
    method: 'get',
})

Controllercff9dfc7b584addc1377d815f55e12d4.definition = {
    methods: ["get","head"],
    url: '/impressum',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/impressum'
*/
Controllercff9dfc7b584addc1377d815f55e12d4.url = (options?: RouteQueryOptions) => {
    return Controllercff9dfc7b584addc1377d815f55e12d4.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/impressum'
*/
Controllercff9dfc7b584addc1377d815f55e12d4.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controllercff9dfc7b584addc1377d815f55e12d4.url(options),
    method: 'get',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/impressum'
*/
Controllercff9dfc7b584addc1377d815f55e12d4.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controllercff9dfc7b584addc1377d815f55e12d4.url(options),
    method: 'head',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/impressum'
*/
const Controllercff9dfc7b584addc1377d815f55e12d4Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: Controllercff9dfc7b584addc1377d815f55e12d4.url(options),
    method: 'get',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/impressum'
*/
Controllercff9dfc7b584addc1377d815f55e12d4Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: Controllercff9dfc7b584addc1377d815f55e12d4.url(options),
    method: 'get',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/impressum'
*/
Controllercff9dfc7b584addc1377d815f55e12d4Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: Controllercff9dfc7b584addc1377d815f55e12d4.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

Controllercff9dfc7b584addc1377d815f55e12d4.form = Controllercff9dfc7b584addc1377d815f55e12d4Form
/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/dashboard'
*/
const Controller42a740574ecbfbac32f8cc353fc32db9 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller42a740574ecbfbac32f8cc353fc32db9.url(options),
    method: 'get',
})

Controller42a740574ecbfbac32f8cc353fc32db9.definition = {
    methods: ["get","head"],
    url: '/dashboard',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/dashboard'
*/
Controller42a740574ecbfbac32f8cc353fc32db9.url = (options?: RouteQueryOptions) => {
    return Controller42a740574ecbfbac32f8cc353fc32db9.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/dashboard'
*/
Controller42a740574ecbfbac32f8cc353fc32db9.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller42a740574ecbfbac32f8cc353fc32db9.url(options),
    method: 'get',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/dashboard'
*/
Controller42a740574ecbfbac32f8cc353fc32db9.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controller42a740574ecbfbac32f8cc353fc32db9.url(options),
    method: 'head',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/dashboard'
*/
const Controller42a740574ecbfbac32f8cc353fc32db9Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: Controller42a740574ecbfbac32f8cc353fc32db9.url(options),
    method: 'get',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/dashboard'
*/
Controller42a740574ecbfbac32f8cc353fc32db9Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: Controller42a740574ecbfbac32f8cc353fc32db9.url(options),
    method: 'get',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/dashboard'
*/
Controller42a740574ecbfbac32f8cc353fc32db9Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: Controller42a740574ecbfbac32f8cc353fc32db9.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

Controller42a740574ecbfbac32f8cc353fc32db9.form = Controller42a740574ecbfbac32f8cc353fc32db9Form
/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/settings/appearance'
*/
const Controllere19ee86e9cf603ce1a59a1ec5d21dec5 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controllere19ee86e9cf603ce1a59a1ec5d21dec5.url(options),
    method: 'get',
})

Controllere19ee86e9cf603ce1a59a1ec5d21dec5.definition = {
    methods: ["get","head"],
    url: '/settings/appearance',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/settings/appearance'
*/
Controllere19ee86e9cf603ce1a59a1ec5d21dec5.url = (options?: RouteQueryOptions) => {
    return Controllere19ee86e9cf603ce1a59a1ec5d21dec5.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/settings/appearance'
*/
Controllere19ee86e9cf603ce1a59a1ec5d21dec5.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controllere19ee86e9cf603ce1a59a1ec5d21dec5.url(options),
    method: 'get',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/settings/appearance'
*/
Controllere19ee86e9cf603ce1a59a1ec5d21dec5.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controllere19ee86e9cf603ce1a59a1ec5d21dec5.url(options),
    method: 'head',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/settings/appearance'
*/
const Controllere19ee86e9cf603ce1a59a1ec5d21dec5Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: Controllere19ee86e9cf603ce1a59a1ec5d21dec5.url(options),
    method: 'get',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/settings/appearance'
*/
Controllere19ee86e9cf603ce1a59a1ec5d21dec5Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: Controllere19ee86e9cf603ce1a59a1ec5d21dec5.url(options),
    method: 'get',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/settings/appearance'
*/
Controllere19ee86e9cf603ce1a59a1ec5d21dec5Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: Controllere19ee86e9cf603ce1a59a1ec5d21dec5.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

Controllere19ee86e9cf603ce1a59a1ec5d21dec5.form = Controllere19ee86e9cf603ce1a59a1ec5d21dec5Form

const Controller = {
    '/agb': Controllere0ac0b42e67455fe4ca34384c73b6f22,
    '/datenschutz': Controllerbdac400a0ce7e1d93c22d58bb3768ce2,
    '/impressum': Controllercff9dfc7b584addc1377d815f55e12d4,
    '/dashboard': Controller42a740574ecbfbac32f8cc353fc32db9,
    '/settings/appearance': Controllere19ee86e9cf603ce1a59a1ec5d21dec5,
}

export default Controller