const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */
//  mix.js('resources/js/app.js', 'public/js')
//  .postCss('resources/css/app.css', 'public/css', [
//      //
//  ]);

 mix.disableNotifications();
 
//  mix.styles('public/css/default.css', 'public/css/default-mix.css').version();
//  mix.styles('public/css/all.css', 'public/css/all.css').version();
//  mix.scripts('public/js/config.js','public/js/config-mix.js').version();
//  mix.scripts('public/js/all.js','public/js/all.js').version();

 mix.copy('resources/js/app/adminInscriptionGCompetition.js','public/js/adminInscriptionGCompetition.js').version();
 mix.copy('resources/js/app/adminInscriptionGTraining.js','public/js/adminInscriptionGTraining.js').version();
 mix.copy('resources/js/app/assist.js','public/js/assist.js').version();
 mix.copy('resources/js/app/day_schedules.js','public/js/day_schedules.js').version();
 mix.copy('resources/js/app/inscriptions.js','public/js/inscriptions.js').version();
 mix.copy('resources/js/app/inscriptions_form.js','public/js/inscriptions_form.js').version();
 mix.copy('resources/js/app/matches_form.js','public/js/matches_form.js').version();
 mix.copy('resources/js/app/matches_functions.js','public/js/matches_functions.js').version();
 mix.copy('resources/js/app/payments.js','public/js/payments.js').version();
 mix.copy('resources/js/app/trainingGroupIndex.js','public/js/trainingGroupIndex.js').version();
//  mix.copy('resources/js/app/formPlayer.js','public/js/formPlayer.js').version();