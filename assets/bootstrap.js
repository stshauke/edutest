// assets/bootstrap.js

import { Application } from '@hotwired/stimulus';
import { definitionsFromContext } from '@hotwired/stimulus-webpack-helpers';

// Démarre l'application Stimulus
window.Stimulus = Application.start();

// Charge automatiquement tous les contrôleurs du dossier ./controllers
const context = require.context('./controllers', true, /\.js$/);
Stimulus.load(definitionsFromContext(context));
