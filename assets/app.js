/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './css/app.css';

// start the Stimulus application
import './bootstrap';



import ReactDOM from 'react-dom';
import React from 'react';
import store from './js/store'
import {Provider} from 'react-redux'
import {MemoryRouter} from 'react-router-dom';

import * as actionCreators from './js/actions/conversation'

import App from './js/components/App';

store.dispatch(actionCreators.setUsername(document.querySelector('#app').dataset.username));

ReactDOM.render((
    <Provider store={store}>
        <MemoryRouter>
            <App/>
        </MemoryRouter>
    </Provider>
), document.getElementById('app'));