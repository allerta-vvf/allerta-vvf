import Promise from 'promise-polyfill';
import {fetch as fetchPolyfill} from 'whatwg-fetch'

export {
    Promise,
    fetchPolyfill as fetch
};